<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Expert;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContactVerificationService
{
    protected WhatsAppOtpService $whatsAppOtpService;

    public function __construct(WhatsAppOtpService $whatsAppOtpService)
    {
        $this->whatsAppOtpService = $whatsAppOtpService;
    }

    /**
     * Determine if the current authenticated user/session needs contact verification.
     * Evaluates across Expert, POS, Business, and Front (Web) panels.
     *
     * @param Request|null $request
     * @return array|null Returns target metadata if verification is required, or null if all verified.
     */
    public function getPendingVerificationTarget(?Request $request = null): ?array
    {
        // 1. Expert Panel Check
        if (Auth::guard('expert')->check()) {
            /** @var Expert $expert */
            $expert = Auth::guard('expert')->user();
            $user = User::select('id', 'first_name', 'last_name', 'contact', 'is_contact_verify')
                ->find($expert->user_id);

            if (!$user || empty($user->contact) || !$user->is_contact_verify) {
                return [
                    'guard' => 'expert',
                    'panel' => 'expert',
                    'panel_title' => 'Expert Workspace',
                    'type' => 'user',
                    'entity_id' => $user?->id ?? $expert->user_id,
                    'entity_name' => trim(($user?->first_name ?? '') . ' ' . ($user?->last_name ?? '')) ?: 'Expert',
                    'contact' => $user?->contact ? (string) $user->contact : null,
                    'is_contact_verify' => (bool) ($user?->is_contact_verify ?? false),
                    'is_missing' => empty($user?->contact),
                    'return_url' => route('expert.dashboard'),
                ];
            }

            return null;
        }

        // 2. POS Panel Check
        if (Auth::guard('pos')->check()) {
            /** @var User $posUser */
            $posUser = Auth::guard('pos')->user();

            // First: POS user personal contact
            if (empty($posUser->contact) || !$posUser->is_contact_verify) {
                return [
                    'guard' => 'pos',
                    'panel' => 'pos',
                    'panel_title' => 'POS Terminal',
                    'type' => 'user',
                    'entity_id' => $posUser->id,
                    'entity_name' => trim($posUser->first_name . ' ' . $posUser->last_name) ?: 'Staff Member',
                    'contact' => $posUser->contact ? (string) $posUser->contact : null,
                    'is_contact_verify' => (bool) $posUser->is_contact_verify,
                    'is_missing' => empty($posUser->contact),
                    'return_url' => route('pos.dashboard'),
                ];
            }

            // Second: If business owner in POS, check business contact
            if ($posUser->role === 'Business') {
                $posBusinessId = session('pos_business_id') ?? $posUser->business_id ?? Business::where('owner_id', $posUser->id)->value('id');

                if ($posBusinessId) {
                    $business = Business::select('id', 'name', 'contact', 'is_contact_verify')->find($posBusinessId);

                    if ($business && (empty($business->contact) || !$business->is_contact_verify)) {
                        return [
                            'guard' => 'pos',
                            'panel' => 'pos',
                            'panel_title' => 'POS Terminal',
                            'type' => 'business',
                            'entity_id' => $business->id,
                            'entity_name' => $business->name ?: 'Business',
                            'contact' => $business->contact ? (string) $business->contact : null,
                            'is_contact_verify' => (bool) $business->is_contact_verify,
                            'is_missing' => empty($business->contact),
                            'return_url' => route('pos.dashboard'),
                        ];
                    }
                }
            }

            return null;
        }

        // 3. Web Guard (Front & Business Manager)
        if (Auth::guard('web')->check()) {
            /** @var User $webUser */
            $webUser = Auth::guard('web')->user();

            // First: Personal user contact verification
            if (empty($webUser->contact) || !$webUser->is_contact_verify) {
                $isBusinessUser = $webUser->role === 'Business' || ($request && $request->is('business-manager*'));

                return [
                    'guard' => 'web',
                    'panel' => $isBusinessUser ? 'business' : 'front',
                    'panel_title' => $isBusinessUser ? 'Business Manager' : 'Customer Account',
                    'type' => 'user',
                    'entity_id' => $webUser->id,
                    'entity_name' => trim($webUser->first_name . ' ' . $webUser->last_name) ?: 'User',
                    'contact' => $webUser->contact ? (string) $webUser->contact : null,
                    'is_contact_verify' => (bool) $webUser->is_contact_verify,
                    'is_missing' => empty($webUser->contact),
                    'return_url' => $isBusinessUser ? route('business.dashboard') : route('home'),
                ];
            }

            // Second: If in Business role or accessing Business Manager, check business contact
            if ($webUser->role === 'Business' || ($request && $request->is('business-manager*'))) {
                $businessId = session('current_business_id') ?? $webUser->business_id ?? Business::where('owner_id', $webUser->id)->value('id');

                if ($businessId) {
                    $business = Business::select('id', 'name', 'contact', 'is_contact_verify')->find($businessId);

                    if ($business && (empty($business->contact) || !$business->is_contact_verify)) {
                        return [
                            'guard' => 'web',
                            'panel' => 'business',
                            'panel_title' => 'Business Manager',
                            'type' => 'business',
                            'entity_id' => $business->id,
                            'entity_name' => $business->name ?: 'Business',
                            'contact' => $business->contact ? (string) $business->contact : null,
                            'is_contact_verify' => (bool) $business->is_contact_verify,
                            'is_missing' => empty($business->contact),
                            'return_url' => route('business.dashboard'),
                        ];
                    }
                }
            }

            return null;
        }

        return null;
    }

    /**
     * Send OTP to the given contact for the currently pending target.
     *
     * @param string $contact
     * @param string|null $templateName
     * @param string|null $language
     * @return array
     */
    public function sendOtp(string $contact, ?string $templateName = null, ?string $language = null): array
    {
        $target = $this->getPendingVerificationTarget();

        if (!$target) {
            return [
                'success' => false,
                'message' => 'No pending contact verification required.',
            ];
        }

        $cleanContact = $this->whatsAppOtpService->sanitizeContact($contact);

        if (strlen($cleanContact) !== 10) {
            return [
                'success' => false,
                'message' => 'Please enter a valid 10-digit mobile number.',
            ];
        }

        return $this->whatsAppOtpService->generateAndSendOtp(
            contact: $cleanContact,
            templateName: $templateName,
            language: $language,
            source: 'whatsapp',
            type: $target['type'],
            ignoreId: $target['entity_id']
        );
    }

    /**
     * Verify OTP and update verification flag on the target model.
     *
     * @param string $contact
     * @param string $otp
     * @return array
     */
    public function verifyOtp(string $contact, string $otp): array
    {
        $target = $this->getPendingVerificationTarget();

        if (!$target) {
            return [
                'success' => false,
                'message' => 'No pending contact verification required.',
            ];
        }

        $cleanContact = $this->whatsAppOtpService->sanitizeContact($contact);

        $verifyResult = $this->whatsAppOtpService->verifyOtp(
            contact: $cleanContact,
            otp: $otp,
            source: 'whatsapp',
            type: $target['type']
        );

        if (!$verifyResult['success']) {
            return $verifyResult;
        }

        // Persist verified contact and flag directly to the target record
        DB::transaction(function () use ($target, $cleanContact) {
            if ($target['type'] === 'user') {
                $user = User::select('id', 'contact', 'is_contact_verify')->lockForUpdate()->find($target['entity_id']);
                if ($user) {
                    $user->contact = $cleanContact;
                    $user->is_contact_verify = 1;
                    $user->save();
                }

                if (session()->has('user_details')) {
                    $userDetails = session('user_details');
                    if (is_object($userDetails)) {
                        $userDetails->contact = $cleanContact;
                        $userDetails->is_contact_verify = 1;
                        session(['user_details' => $userDetails]);
                    }
                }
            } elseif ($target['type'] === 'business') {
                $business = Business::select('id', 'contact', 'is_contact_verify')->lockForUpdate()->find($target['entity_id']);
                if ($business) {
                    $business->contact = $cleanContact;
                    $business->is_contact_verify = 1;
                    $business->save();
                }

                if (session()->has('current_business_details')) {
                    $bizDetails = session('current_business_details');
                    if (is_object($bizDetails)) {
                        $bizDetails->contact = $cleanContact;
                        $bizDetails->is_contact_verify = 1;
                        session(['current_business_details' => $bizDetails]);
                    }
                }
            }
        });

        // Check if there is another pending verification target (e.g., Business after User)
        $nextTarget = $this->getPendingVerificationTarget();

        if ($nextTarget) {
            return [
                'success' => true,
                'message' => 'Personal contact verified! Please now verify your business contact.',
                'redirect' => route('verify.contact'),
                'has_next' => true,
            ];
        }

        $redirectUrl = $this->resolveRedirectUrl($target);

        return [
            'success' => true,
            'message' => 'Contact number verified successfully!',
            'redirect' => $redirectUrl,
            'has_next' => false,
        ];
    }

    /**
     * Resolve the target panel URL after successful verification.
     *
     * @param array $target
     * @return string
     */
    public function resolveRedirectUrl(array $target): string
    {
        $returnUrl = session('contact_verify_return_url');
        session()->forget('contact_verify_return_url');

        if ($returnUrl &&
            !str_contains($returnUrl, 'verify-contact') &&
            !str_contains($returnUrl, 'logout') &&
            !str_contains($returnUrl, 'login')) {
            return $returnUrl;
        }

        return $target['return_url'] ?? route('home');
    }
}

