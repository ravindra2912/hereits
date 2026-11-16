<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class WhatsAppOtpService
{
    protected WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Sanitize contact number to standard 10-digit format.
     */
    public function sanitizeContact(string $contact): string
    {
        $digits = preg_replace('/\D+/', '', $contact);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
    }

    /**
     * Generate and dispatch WhatsApp OTP.
     *
     * @param string $contact
     * @return array
     */
    public function generateAndSendOtp(string $contact): array
    {
        $cleanContact = $this->sanitizeContact($contact);

        if (strlen($cleanContact) !== 10) {
            return [
                'success' => false,
                'message' => 'Please enter a valid 10-digit mobile number.',
            ];
        }

        // Check if phone number is already registered
        if (Business::where('contact', $cleanContact)->exists()) {
            return [
                'success' => false,
                'message' => 'This contact number is already registered with another business.',
            ];
        }

        // Check rate limiting / cooldown
        $cooldownKey = "whatsapp_otp_cooldown_{$cleanContact}";
        if (Cache::has($cooldownKey)) {
            $expireAt = Cache::get($cooldownKey);
            $remaining = max(1, $expireAt - now()->timestamp);
            return [
                'success' => false,
                'message' => "Please wait {$remaining} seconds before requesting a new OTP.",
                'cooldown' => $remaining,
            ];
        }

        // Generate 6-digit OTP
        $otp = (string) random_int(100000, 999999);
        $expiryMinutes = config('services.whatsapp.otp_expiry_minutes', 10);

        // Store hashed OTP in cache
        $otpKey = "whatsapp_otp_{$cleanContact}";
        Cache::put($otpKey, [
            'otp' => Hash::make($otp),
            'created_at' => now()->timestamp,
            'attempts' => 0,
        ], now()->addMinutes($expiryMinutes));

        // Set 60-second cooldown
        Cache::put($cooldownKey, now()->timestamp + 60, 60);

        // Dispatch via WhatsApp Meta API
        $sendResult = $this->whatsAppService->sendOtp($cleanContact, $otp);

        if (!$sendResult['success']) {
            Cache::forget($otpKey);
            Cache::forget($cooldownKey);
            return [
                'success' => false,
                'message' => $sendResult['message'] ?? 'Failed to send WhatsApp verification code. Please try again.',
            ];
        }

        $maskedNumber = '+91 ' . substr($cleanContact, 0, 2) . '******' . substr($cleanContact, -2);

        return [
            'success' => true,
            'message' => "Verification code sent to WhatsApp number {$maskedNumber}.",
            'cooldown' => 60,
        ];
    }

    /**
     * Verify the entered WhatsApp OTP.
     *
     * @param string $contact
     * @param string $otp
     * @return array
     */
    public function verifyOtp(string $contact, string $otp): array
    {
        $cleanContact = $this->sanitizeContact($contact);
        $otpKey = "whatsapp_otp_{$cleanContact}";

        $cachedData = Cache::get($otpKey);

        if (!$cachedData || !isset($cachedData['otp'])) {
            return [
                'success' => false,
                'message' => 'Verification code expired or not found. Please request a new code.',
            ];
        }

        // Check max attempts
        if (($cachedData['attempts'] ?? 0) >= 5) {
            Cache::forget($otpKey);
            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new verification code.',
            ];
        }

        if (!Hash::check($otp, $cachedData['otp'])) {
            $cachedData['attempts'] = ($cachedData['attempts'] ?? 0) + 1;
            Cache::put($otpKey, $cachedData, now()->addMinutes(config('services.whatsapp.otp_expiry_minutes', 10)));

            $remainingAttempts = 5 - $cachedData['attempts'];
            return [
                'success' => false,
                'message' => "Invalid verification code. {$remainingAttempts} attempt(s) remaining.",
            ];
        }

        // OTP Verified successfully!
        Cache::forget($otpKey);
        Cache::forget("whatsapp_otp_cooldown_{$cleanContact}");

        $verificationToken = Str::random(40);

        // Store verification in Cache for 60 minutes
        Cache::put("whatsapp_verified_{$cleanContact}", $verificationToken, now()->addMinutes(60));

        // Also store in session for multi-layered validation
        session()->put("whatsapp_verified_{$cleanContact}", $verificationToken);

        return [
            'success' => true,
            'message' => 'WhatsApp contact number verified successfully!',
            'token' => $verificationToken,
        ];
    }

    /**
     * Check if a contact number has a valid verified token.
     *
     * @param string $contact
     * @param string|null $token
     * @return bool
     */
    public function isContactVerified(string $contact, ?string $token = null): bool
    {
        $cleanContact = $this->sanitizeContact($contact);
        $cacheToken = Cache::get("whatsapp_verified_{$cleanContact}");
        $sessionToken = session("whatsapp_verified_{$cleanContact}");

        if (!empty($token)) {
            return ($cacheToken && hash_equals($cacheToken, $token)) ||
                   ($sessionToken && hash_equals($sessionToken, $token));
        }

        return !empty($cacheToken) || !empty($sessionToken);
    }

    /**
     * Invalidate verification token once registration is complete.
     *
     * @param string $contact
     * @return void
     */
    public function consumeVerification(string $contact): void
    {
        $cleanContact = $this->sanitizeContact($contact);
        Cache::forget("whatsapp_verified_{$cleanContact}");
        session()->forget("whatsapp_verified_{$cleanContact}");
    }
}

