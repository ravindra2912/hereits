<?php

namespace App\Services;

use App\Models\Business;
use App\Models\OtpManagement;
use Illuminate\Support\Facades\Log;
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
     * Generate and dispatch WhatsApp OTP, recording into otp_management table.
     *
     * @param string $contact
     * @param string|null $templateName
     * @param string|null $language
     * @param string $source
     * @return array
     */
    public function generateAndSendOtp(
        string $contact,
        ?string $templateName = null,
        ?string $language = null,
        string $source = 'whatsapp'
    ): array {
        $cleanContact = $this->sanitizeContact($contact);

        if (strlen($cleanContact) !== 10) {
            return [
                'success' => false,
                'message' => 'Please enter a valid 10-digit mobile number.',
            ];
        }

        // Prevent registration if phone number already belongs to an existing business
        if (Business::where('contact', $cleanContact)->exists()) {
            return [
                'success' => false,
                'message' => 'This contact number is already registered with another business.',
            ];
        }

        // Rate limiting / Cooldown check directly from otp_management table (60 seconds)
        $latestRecord = OtpManagement::select('id', 'created_at')
            ->where('contact', $cleanContact)
            ->where('source', $source)
            ->latest('id')
            ->first();

        if ($latestRecord && $latestRecord->created_at) {
            $elapsedSeconds = $latestRecord->created_at->diffInSeconds(now());
            if ($elapsedSeconds < 60) {
                $remaining = 60 - $elapsedSeconds;
                return [
                    'success' => false,
                    'message' => "Please wait {$remaining} seconds before requesting a new OTP.",
                    'cooldown' => $remaining,
                ];
            }
        }

        // Expire any existing pending OTPs for this contact & source to ensure only 1 active code
        OtpManagement::where('contact', $cleanContact)
            ->where('source', $source)
            ->where('status', 'pending')
            ->update(['status' => 'expired']);

        // Generate secure 6-digit numeric OTP
        $otp = (string) random_int(100000, 999999);
        $expiryMinutes = (int) config('services.whatsapp.otp_expiry_minutes', 10);

        // Store OTP entry in otp_management table
        $otpEntry = OtpManagement::create([
            'contact' => $cleanContact,
            'otp' => $otp,
            'source' => $source,
            'valid' => $expiryMinutes,
            'status' => 'pending',
            'attempts' => 0,
        ]);

        Log::info("WhatsApp OTP Generated and Logged to Table", [
            'id' => $otpEntry->id,
            'contact' => $cleanContact,
            'otp' => $otp,
            'source' => $source,
            'valid_minutes' => $expiryMinutes,
            'template' => $templateName,
            'language' => $language,
        ]);

        // Dispatch via WhatsApp Meta Cloud API
        $sendResult = $this->whatsAppService->sendOtp($cleanContact, $otp, $templateName, $language);

        if (!$sendResult['success']) {
            $otpEntry->update(['status' => 'failed']);

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
     * Verify the entered WhatsApp OTP using the otp_management table (No Cache).
     *
     * @param string $contact
     * @param string $otp
     * @param string $source
     * @return array
     */
    public function verifyOtp(string $contact, string $otp, string $source = 'whatsapp'): array
    {
        $cleanContact = $this->sanitizeContact($contact);

        Log::info("WhatsApp OTP Verification Attempt", [
            'contact' => $cleanContact,
            'source' => $source,
        ]);

        // Query the latest pending OTP entry for this contact from otp_management table
        $otpRecord = OtpManagement::where('contact', $cleanContact)
            ->where('source', $source)
            ->where('status', 'pending')
            ->latest('id')
            ->first();

        if (!$otpRecord) {
            Log::warning("WhatsApp OTP Verification Failed: No pending OTP found in table", [
                'contact' => $cleanContact,
            ]);

            return [
                'success' => false,
                'message' => 'Verification code not found or already used. Please request a new code.',
            ];
        }

        // Check expiration based on the `valid` column (minutes) and `created_at`
        if ($otpRecord->created_at->addMinutes($otpRecord->valid)->isPast()) {
            $otpRecord->update(['status' => 'expired']);

            Log::warning("WhatsApp OTP Verification Failed: OTP expired", [
                'id' => $otpRecord->id,
                'contact' => $cleanContact,
            ]);

            return [
                'success' => false,
                'message' => 'Verification code has expired. Please request a new code.',
            ];
        }

        // Check maximum failed attempts limit
        if ($otpRecord->attempts >= 5) {
            $otpRecord->update(['status' => 'expired']);

            Log::warning("WhatsApp OTP Verification Failed: Max attempts reached", [
                'id' => $otpRecord->id,
                'contact' => $cleanContact,
            ]);

            return [
                'success' => false,
                'message' => 'Too many failed attempts. Please request a new verification code.',
            ];
        }

        // Verify OTP match
        if (trim($otpRecord->otp) !== trim($otp)) {
            $otpRecord->increment('attempts');
            $remainingAttempts = max(0, 5 - (int) $otpRecord->attempts);

            if ($remainingAttempts <= 0) {
                $otpRecord->update(['status' => 'expired']);

                return [
                    'success' => false,
                    'message' => 'Too many failed attempts. Please request a new verification code.',
                ];
            }

            Log::warning("WhatsApp OTP Verification Failed: Incorrect code", [
                'id' => $otpRecord->id,
                'contact' => $cleanContact,
                'attempts' => $otpRecord->attempts,
                'remaining' => $remainingAttempts,
            ]);

            return [
                'success' => false,
                'message' => "Invalid verification code. {$remainingAttempts} attempt(s) remaining.",
            ];
        }

        // OTP is valid: Generate verification token & mark as verified in table
        $verificationToken = Str::random(40);

        $otpRecord->update([
            'status' => 'verified',
            'token' => $verificationToken,
            'verified_at' => now(),
        ]);

        // Keep session token for seamless multi-factor browser state
        session()->put("whatsapp_verified_{$cleanContact}", $verificationToken);

        Log::info("WhatsApp OTP Verified Successfully in Table", [
            'id' => $otpRecord->id,
            'contact' => $cleanContact,
            'token' => $verificationToken,
        ]);

        return [
            'success' => true,
            'message' => 'WhatsApp contact number verified successfully!',
            'token' => $verificationToken,
        ];
    }

    /**
     * Check if a contact number has a valid verified token in otp_management table.
     *
     * @param string $contact
     * @param string|null $token
     * @param string $source
     * @return bool
     */
    public function isContactVerified(string $contact, ?string $token = null, string $source = 'whatsapp'): bool
    {
        $cleanContact = $this->sanitizeContact($contact);

        // Check otp_management table for a verified record within last 2 hours
        $query = OtpManagement::where('contact', $cleanContact)
            ->where('source', $source)
            ->where('status', 'verified')
            ->where('verified_at', '>=', now()->subHours(2));

        if (!empty($token)) {
            $query->where('token', $token);
        }

        return $query->exists();
    }

    /**
     * Consume verification record in otp_management table once registration is complete.
     *
     * @param string $contact
     * @param string $source
     * @return void
     */
    public function consumeVerification(string $contact, string $source = 'whatsapp'): void
    {
        $cleanContact = $this->sanitizeContact($contact);

        OtpManagement::where('contact', $cleanContact)
            ->where('source', $source)
            ->where('status', 'verified')
            ->update(['status' => 'used']);

        session()->forget("whatsapp_verified_{$cleanContact}");

        Log::info("WhatsApp OTP Verification Consumed", [
            'contact' => $cleanContact,
            'source' => $source,
        ]);
    }
}
