<?php

namespace App\Services;

use App\Jobs\SendFirebaseNotificationJob;
use App\Models\Admin;
use App\Models\Business;
use App\Models\Expert;
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
            $credentialsPath = config('services.firebase.credentials') ?: base_path('firebase-credentials.json');

            if (!file_exists($credentialsPath)) {
                Log::warning("FCM Error: Credentials file not found at [{$credentialsPath}].");
                return null;
            }

            $serviceAccount = json_decode(file_get_contents($credentialsPath), true);
            if (!$serviceAccount || empty($serviceAccount['client_email']) || empty($serviceAccount['private_key'])) {
                Log::error("FCM Error: Invalid service account format in [{$credentialsPath}].");
                return null;
            }

            $now = time();
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];
            $payload = [
                'iss'   => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            $encodedHeader = self::base64UrlEncode(json_encode($header));
            $encodedPayload = self::base64UrlEncode(json_encode($payload));
            $signatureInput = "{$encodedHeader}.{$encodedPayload}";

            $privateKey = openssl_pkey_get_private($serviceAccount['private_key']);
            if (!$privateKey) {
                Log::error("FCM Error: Invalid private key in Firebase credentials.");
                return null;
            }

            openssl_sign($signatureInput, $signature, $privateKey, 'SHA256');
            $jwt = "{$signatureInput}." . self::base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            Log::error("FCM Error fetching OAuth token: " . $response->body());
            return null;
        });
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Send raw push notification via FCM HTTP v1.
     */
    public static function sendPush(string $fcmToken, string $title, string $message, ?string $redirectUrl = null, array $data = []): bool
    {
        $accessToken = self::getAccessToken();
        if (!$accessToken) {
            Log::warning("FCM Push aborted: No valid OAuth2 access token.");
            return false;
        }

        $projectId = config('services.firebase.project_id');
        if (!$projectId) {
            $credentialsPath = config('services.firebase.credentials') ?: base_path('firebase-credentials.json');
            if (file_exists($credentialsPath)) {
                $serviceAccount = json_decode(file_get_contents($credentialsPath), true);
                $projectId = $serviceAccount['project_id'] ?? null;
            }
        }

        if (!$projectId) {
            Log::error("FCM Push Error: No project_id found in config or credentials.");
            return false;
        }

        $fcmUrl = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

        $customData = array_map(fn($v) => (string) $v, $data);
        if ($redirectUrl) {
            $customData['click_url'] = (string) $redirectUrl;
        }

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $message,
                ],
                'data' => (object) $customData,
                'webpush' => [
                    'headers' => [
                        'Urgency' => 'high',
                    ],
                    'notification' => [
                        'title' => $title,
                        'body'  => $message,
                        'icon'  => asset('assets/front/images/logo.png'),
                        'badge' => asset('assets/front/images/logo.png'),
                    ],
                    'fcm_options' => [
                        'link' => $redirectUrl ?: url('/'),
                    ],
                ],
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->withHeaders(['Content-Type' => 'application/json; UTF-8'])
                ->post($fcmUrl, $payload);

            if ($response->successful()) {
                return true;
            }

            Log::error("FCM Send Error [{$response->status()}]: " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("FCM Send Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to a User.
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
     * Send notification to a Business Owner.
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
     * Send notification to an Expert.
     */
    public static function sendToExpert(Expert $expert, string $title, string $message, ?string $redirectUrl = null, array $data = []): void
    {
        $user = $expert->relationLoaded('user') && $expert->user
            ? $expert->user
            : User::select('id', 'fcm_token')->find($expert->user_id);

        if (!$user) {
            return;
        }

        // 1. Record in DB notification history
        Notification::create([
            'user_id' => $user->id,
            'business_id' => $expert->business_id,
            'type' => $data['type'] ?? 'expert_appointment',
            'title' => $title,
            'message' => $message,
            'data' => array_merge($data, ['click_url' => $redirectUrl]),
        ]);

        // 2. Collect tokens
        $tokens = [];
        if (!empty($user->fcm_token)) {
            $tokens[] = $user->fcm_token;
        }

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
