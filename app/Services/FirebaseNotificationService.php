<?php

namespace App\Services;

use App\Jobs\SendFirebaseNotificationJob;
use App\Models\Admin;
use App\Models\Business;
use App\Models\Notification;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseNotificationService
{
    /**
     * Get or generate a cached Google OAuth2 Access Token for FCM HTTP v1.
     */
    public static function getAccessToken(): ?string
    {
        return Cache::remember('fcm_v1_access_token', 3300, function () {
            $clientEmail = config('services.firebase.client_email', env('FIREBASE_CLIENT_EMAIL'));
            $privateKey = config('services.firebase.private_key', env('FIREBASE_PRIVATE_KEY'));

            // Fallback to credentials JSON file if not provided directly in .env
            if (empty($clientEmail) || empty($privateKey)) {
                $configuredPath = config('services.firebase.credentials', env('FIREBASE_CREDENTIALS', 'storage/app/firebase/firebase_credentials.json'));
                $credentialsPath = base_path($configuredPath);

                if (file_exists($credentialsPath)) {
                    $jsonContent = file_get_contents($credentialsPath);
                    $credentials = json_decode($jsonContent, true);
                    $clientEmail = $credentials['client_email'] ?? null;
                    $privateKey = $credentials['private_key'] ?? null;
                }
            }

            if (empty($clientEmail) || empty($privateKey)) {
                Log::warning("Firebase credentials missing. Provide FIREBASE_CLIENT_EMAIL and FIREBASE_PRIVATE_KEY in .env, or place service account JSON at storage/app/firebase/firebase_credentials.json");
                return null;
            }

            // Normalize escaped newlines from .env string
            $privateKey = str_replace('\n', "\n", $privateKey);

            $credentials = [
                'client_email' => $clientEmail,
                'private_key' => $privateKey,
            ];

            $now = time();
            $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $claim = self::base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now,
            ]));

            $signatureInput = "{$header}.{$claim}";
            $signature = '';

            if (!openssl_sign($signatureInput, $signature, $credentials['private_key'], 'sha256WithRSAEncryption')) {
                Log::error("Failed to sign JWT for Firebase OAuth2.");
                return null;
            }

            $jwt = "{$signatureInput}." . self::base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error("Failed to retrieve Google Access Token for FCM: " . $response->body());
            return null;
        });
    }

    /**
     * Base64 URL-safe encoder.
     */
    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send direct push notification to an FCM token using FCM HTTP v1.
     */
    public static function sendPush(string $fcmToken, string $title, string $message, ?string $redirectUrl = null, array $data = []): bool
    {
        $accessToken = self::getAccessToken();
        $projectId = config('services.firebase.project_id', env('FIREBASE_PROJECT_ID'));

        if (!$accessToken || !$projectId) {
            Log::warning("Skipping FCM send: Access token or FIREBASE_PROJECT_ID is missing.");
            return false;
        }

        $url = $redirectUrl ?: url('/');

        // In FCM v1 data payload, all values must be string
        $stringData = [];
        foreach (array_merge($data, ['click_url' => $url]) as $key => $val) {
            $stringData[(string) $key] = is_array($val) ? json_encode($val) : (string) $val;
        }

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                ],
                'data' => $stringData,
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high',
                    ],
                    'fcm_options' => [
                        'link' => $url,
                    ],
                    'notification' => [
                        'icon' => asset('assets/images/logo.png'),
                        'badge' => asset('assets/images/badge.png'),
                    ],
                ],
            ],
        ];

        $response = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json; UTF-8'])
            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

        if ($response->failed()) {
            Log::error("FCM v1 delivery error: " . $response->body(), [
                'token' => substr($fcmToken, 0, 15) . '...',
                'title' => $title,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Send notification to a customer/front user.
     */
    public static function sendToUser(User $user, string $title, string $message, ?string $redirectUrl = null, array $data = []): void
    {
        // 1. Record in DB notification history
        Notification::create([
            'user_id' => $user->id,
            'business_id' => $data['business_id'] ?? null,
            'type' => $data['type'] ?? 'user_event',
            'title' => $title,
            'message' => $message,
            'data' => array_merge($data, ['click_url' => $redirectUrl]),
        ]);

        // 2. Collect FCM tokens
        $tokens = [];
        if (!empty($user->fcm_token)) {
            $tokens[] = $user->fcm_token;
        }

        // Also collect any active device tokens from user_devices table
        $deviceTokens = UserDevice::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        $allTokens = array_values(array_filter(array_unique(array_merge($tokens, $deviceTokens))));

        if (!empty($allTokens)) {
            SendFirebaseNotificationJob::dispatch($allTokens, $title, $message, $redirectUrl, $data);
        }
    }

    /**
     * Send notification to a Business Owner & Staff.
     */
    public static function sendToBusiness(Business $business, string $title, string $message, ?string $redirectUrl = null, array $data = []): void
    {
        $owner = User::select('id', 'fcm_token')->find($business->owner_id);
        if (!$owner) {
            return;
        }

        // 1. Record in DB notification history
        Notification::create([
            'user_id' => $owner->id,
            'business_id' => $business->id,
            'type' => $data['type'] ?? 'business_event',
            'title' => $title,
            'message' => $message,
            'data' => array_merge($data, ['click_url' => $redirectUrl]),
        ]);

        // 2. Collect tokens
        $tokens = [];
        if (!empty($owner->fcm_token)) {
            $tokens[] = $owner->fcm_token;
        }

        $deviceTokens = UserDevice::query()
            ->where('user_id', $owner->id)
            ->where('is_active', true)
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        $allTokens = array_values(array_filter(array_unique(array_merge($tokens, $deviceTokens))));

        if (!empty($allTokens)) {
            SendFirebaseNotificationJob::dispatch($allTokens, $title, $message, $redirectUrl, $data);
        }
    }

    /**
     * Send notification to All Admins.
     */
    public static function sendToAdmins(string $title, string $message, ?string $redirectUrl = null, array $data = []): void
    {
        $adminTokens = Admin::query()
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->filter()
            ->values()
            ->toArray();

        if (!empty($adminTokens)) {
            SendFirebaseNotificationJob::dispatch($adminTokens, $title, $message, $redirectUrl, $data);
        }
    }
}
