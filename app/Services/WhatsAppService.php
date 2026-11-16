<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $token;
    protected ?string $phoneNumberId;
    protected string $apiVersion;
    protected ?string $templateName;
    protected string $templateLang;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiVersion = config('services.whatsapp.api_version', 'v21.0');
        $this->templateName = config('services.whatsapp.template_name');
        $this->templateLang = config('services.whatsapp.template_lang', 'en_US');
    }

    /**
     * Send OTP to a WhatsApp phone number.
     *
     * @param string $recipientPhone
     * @param string $otp
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendOtp(string $recipientPhone, string $otp): array
    {
        $formattedPhone = $this->formatPhoneNumber($recipientPhone);

        // If credentials are not configured, handle gracefully (log in dev/local)
        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::warning("Meta WhatsApp Cloud API credentials not configured in .env. OTP for {$formattedPhone} is: {$otp}");

            if (app()->environment(['local', 'testing', 'staging']) || empty($this->token)) {
                return [
                    'success' => true,
                    'message' => 'OTP dispatched (logged in local environment).',
                    'mock' => true,
                ];
            }

            return [
                'success' => false,
                'message' => 'WhatsApp messaging service is currently unconfigured. Please contact support.',
            ];
        }

        try {
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

            // If a template is configured in .env, construct template payload
            if (!empty($this->templateName)) {
                $payload = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $formattedPhone,
                    'type' => 'template',
                    'template' => [
                        'name' => $this->templateName,
                        'language' => [
                            'code' => $this->templateLang,
                        ],
                        'components' => [
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $otp,
                                    ],
                                ],
                            ],
                            [
                                'type' => 'button',
                                'sub_type' => 'url',
                                'index' => '0',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $otp,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];
            } else {
                // Freeform text message payload
                $appName = config('app.name', 'hereits');
                $expiry = config('services.whatsapp.otp_expiry_minutes', 10);
                $bodyText = "Your verification code for {$appName} is: {$otp}. Valid for {$expiry} minutes. Please do not share this code with anyone.";

                $payload = [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $formattedPhone,
                    'type' => 'text',
                    'text' => [
                        'preview_url' => false,
                        'body' => $bodyText,
                    ],
                ];
            }

            $response = Http::timeout(15)
                ->withToken($this->token)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->successful()) {
                Log::info("WhatsApp OTP successfully sent via Meta API to {$formattedPhone}");
                return [
                    'success' => true,
                    'message' => 'OTP sent successfully via WhatsApp.',
                    'data' => $response->json(),
                ];
            }

            $errorData = $response->json();
            $metaErrorMessage = $errorData['error']['message'] ?? 'Meta API error';
            $metaErrorCode = $errorData['error']['code'] ?? 'UNKNOWN';

            Log::error("Meta WhatsApp API Error [{$metaErrorCode}]: {$metaErrorMessage}", [
                'to' => $formattedPhone,
                'response' => $errorData,
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => "Failed to send WhatsApp message: {$metaErrorMessage}",
                'error' => $errorData,
            ];

        } catch (Exception $e) {
            Log::error("Meta WhatsApp Exception: " . $e->getMessage(), [
                'to' => $formattedPhone,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while connecting to WhatsApp service.',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Format a phone number to standard E.164 without leading plus.
     * Defaults 10-digit Indian numbers to country code 91.
     *
     * @param string $phone
     * @return string
     */
    public function formatPhoneNumber(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        if (strlen($digits) === 10) {
            $digits = '91' . $digits;
        }

        return $digits;
    }
}

