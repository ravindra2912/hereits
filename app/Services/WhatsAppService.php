<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected ?string $token;
    protected ?string $phoneNumberId;
    protected ?string $businessAccountId;
    protected string $apiVersion;

    public function __construct()
    {
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->businessAccountId = config('services.whatsapp.business_account_id');

        $rawVersion = (string) (config('services.whatsapp.api_version', 'v21.0') ?: 'v21.0');
        $normalizedVersion = ltrim(trim($rawVersion), 'vV');
        if (!str_contains($normalizedVersion, '.')) {
            $normalizedVersion .= '.0';
        }
        $this->apiVersion = 'v' . $normalizedVersion;
    }

    /**
     * Send a raw or structured WhatsApp message payload using Meta Cloud API.
     *
     * @param array $payload
     * @return array ['success' => bool, 'message' => string, 'data' => mixed, 'error' => mixed]
     */
    public function sendMessage(array $payload): array
    {
        // Validate credentials
        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::warning("Meta WhatsApp Cloud API credentials not configured in .env.", [
                'payload' => $payload,
            ]);

            if (app()->environment(['local', 'testing', 'staging']) || empty($this->token)) {
                return [
                    'success' => true,
                    'message' => 'WhatsApp message dispatched (mocked in local environment).',
                    'mock' => true,
                ];
            }

            return [
                'success' => false,
                'message' => 'WhatsApp messaging service is currently unconfigured. Please contact support.',
            ];
        }

        // Validate required recipient
        if (empty($payload['to'])) {
            return [
                'success' => false,
                'message' => 'Recipient phone number (to) is required.',
            ];
        }

        $formattedPhone = $this->formatPhoneNumber((string) $payload['to']);
        $payload['to'] = $formattedPhone;
        $payload['messaging_product'] = 'whatsapp';
        $payload['recipient_type'] = $payload['recipient_type'] ?? 'individual';

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            // Log outgoing Meta WhatsApp API Request
            Log::info("Meta WhatsApp API Request", [
                'endpoint' => $url,
                'recipient' => $formattedPhone,
                'type' => $payload['type'] ?? 'unknown',
                'payload' => $payload,
            ]);

            $response = Http::timeout(15)
                ->withToken($this->token)
                ->acceptJson()
                ->post($url, $payload);

            $status = $response->status();
            $responseBody = $response->json() ?? $response->body();

            if ($response->successful()) {
                Log::info("Meta WhatsApp API Response [{$status} OK]", [
                    'recipient' => $formattedPhone,
                    'status' => $status,
                    'response' => $responseBody,
                ]);

                return [
                    'success' => true,
                    'message' => 'Message sent successfully via WhatsApp.',
                    'data' => $responseBody,
                ];
            }

            $errorData = is_array($responseBody) ? $responseBody : ['raw' => $responseBody];
            return $this->parseMetaError($status, $errorData, $formattedPhone, $payload);

        } catch (Exception $e) {
            Log::error("Meta WhatsApp API Exception: " . $e->getMessage(), [
                'recipient' => $formattedPhone,
                'endpoint' => $url,
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while connecting to WhatsApp service: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send a standard plain text message via WhatsApp.
     *
     * @param string $recipientPhone
     * @param string $message
     * @param bool $previewUrl
     * @return array
     */
    public function sendTextMessage(string $recipientPhone, string $message, bool $previewUrl = false): array
    {
        $formattedPhone = $this->formatPhoneNumber($recipientPhone);

        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => 'Recipient phone number is required.',
            ];
        }

        if (trim($message) === '') {
            return [
                'success' => false,
                'message' => 'Message text cannot be empty.',
            ];
        }

        return $this->sendMessage([
            'to' => $formattedPhone,
            'type' => 'text',
            'text' => [
                'preview_url' => $previewUrl,
                'body' => $message,
            ],
        ]);
    }

    /**
     * Send an approved template message via WhatsApp.
     * Validates required info only: does not select default template if null.
     *
     * @param string $recipientPhone
     * @param string|null $templateName
     * @param string $language
     * @param array $components
     * @return array
     */
    public function sendTemplate(
        string $recipientPhone,
        ?string $templateName,
        string $language = 'en_US',
        array $components = []
    ): array {
        $formattedPhone = $this->formatPhoneNumber($recipientPhone);

        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => 'Recipient phone number is required.',
            ];
        }

        // Validate required template name (do not select default if null)
        if (empty($templateName)) {
            return [
                'success' => false,
                'message' => 'WhatsApp template name is required.',
            ];
        }

        $template = [
            'name' => $templateName,
            'language' => [
                'code' => $language ?: 'en_US',
            ],
        ];

        if (!empty($components)) {
            $template['components'] = $components;
        }

        return $this->sendMessage([
            'to' => $formattedPhone,
            'type' => 'template',
            'template' => $template,
        ]);
    }

    /**
     * Manage OTP flow separately: constructs OTP template components & handles auto-retries.
     *
     * @param string $recipientPhone
     * @param string $otp
     * @param string|null $templateName
     * @param string|null $language
     * @param array|null $customComponents
     * @return array
     */
    public function sendOtp(
        string $recipientPhone,
        string $otp,
        ?string $templateName = null,
        ?string $language = null,
        ?array $customComponents = null
    ): array {
        $formattedPhone = $this->formatPhoneNumber($recipientPhone);

        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => 'Recipient phone number is required.',
            ];
        }

        if (empty($otp)) {
            return [
                'success' => false,
                'message' => 'OTP code is required.',
            ];
        }

        // Resolve template name: uses provided template, or configured OTP template from .env
        $activeTemplate = $templateName ?: config('services.whatsapp.template_name');

        if (empty($activeTemplate)) {
            return [
                'success' => false,
                'message' => 'WhatsApp template name is required to send OTP. Please specify a template name or set META_WHATSAPP_OTP_TEMPLATE in .env.',
            ];
        }

        $activeLang = $language ?: config('services.whatsapp.template_lang', 'en_US');

        // Build OTP components
        if (!empty($customComponents)) {
            $components = $customComponents;
        } else {
            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $otp,
                        ],
                    ],
                ],
            ];

            $buttonType = config('services.whatsapp.template_button_type');
            if (empty($buttonType) && $activeTemplate === 'hereits_otp') {
                $buttonType = 'url';
            }

            if ($buttonType === 'copy_code') {
                $components[] = [
                    'type' => 'button',
                    'sub_type' => 'copy_code',
                    'index' => '0',
                    'parameters' => [
                        [
                            'type' => 'coupon_code',
                            'coupon_code' => $otp,
                        ],
                    ],
                ];
            } elseif ($buttonType === 'url') {
                $components[] = [
                    'type' => 'button',
                    'sub_type' => 'url',
                    'index' => '0',
                    'parameters' => [
                        [
                            'type' => 'text',
                            'text' => $otp,
                        ],
                    ],
                ];
            }
        }

        // Send OTP template
        $result = $this->sendTemplate($formattedPhone, $activeTemplate, $activeLang, $components);

        // If template failed due to button / component parameter mismatch, auto-retry with corrected components
        if (!$result['success'] && isset($result['error'])) {
            $errorData = $result['error'];
            $errMessage = $errorData['error']['message'] ?? '';
            $errDetails = $errorData['error']['error_data']['details'] ?? '';
            $errCode = $errorData['error']['code'] ?? 0;
            $allErrText = $errMessage . ' ' . $errDetails;

            // Case 1: Template failed because button parameter was sent, but template has no button
            if (count($components) > 1 && (stripos($allErrText, 'does not match') !== false || stripos($allErrText, 'invalid button') !== false || $errCode == 132007)) {
                Log::warning("Retrying OTP template with body component only...", [
                    'template' => $activeTemplate,
                    'reason' => $allErrText,
                ]);

                $retryComponents = [
                    [
                        'type' => 'body',
                        'parameters' => [
                            ['type' => 'text', 'text' => $otp],
                        ],
                    ],
                ];

                return $this->sendTemplate($formattedPhone, $activeTemplate, $activeLang, $retryComponents);
            }

            // Case 2: Template expects a button parameter but we sent body only
            if (count($components) === 1 && ($errCode == 131008 || stripos($allErrText, 'button') !== false || stripos($allErrText, 'requires a parameter') !== false)) {
                $isUrlButton = stripos($allErrText, 'url') !== false;

                Log::warning("Retrying OTP template with required button component...", [
                    'template' => $activeTemplate,
                    'button_type' => $isUrlButton ? 'url' : 'copy_code',
                    'reason' => $allErrText,
                ]);

                $retryComponents = $components;
                $retryComponents[] = $isUrlButton
                    ? [
                        'type' => 'button',
                        'sub_type' => 'url',
                        'index' => '0',
                        'parameters' => [
                            ['type' => 'text', 'text' => $otp],
                        ],
                    ]
                    : [
                        'type' => 'button',
                        'sub_type' => 'copy_code',
                        'index' => '0',
                        'parameters' => [
                            ['type' => 'coupon_code', 'coupon_code' => $otp],
                        ],
                    ];

                return $this->sendTemplate($formattedPhone, $activeTemplate, $activeLang, $retryComponents);
            }
        }

        return $result;
    }

    /**
     * Send media message (image, document, audio, video).
     *
     * @param string $recipientPhone
     * @param string $mediaType ('image', 'document', 'audio', 'video', 'sticker')
     * @param string $mediaUrlOrId (Public URL or Meta media ID)
     * @param string|null $caption
     * @param string|null $filename (For documents)
     * @return array
     */
    public function sendMedia(
        string $recipientPhone,
        string $mediaType,
        string $mediaUrlOrId,
        ?string $caption = null,
        ?string $filename = null
    ): array {
        $formattedPhone = $this->formatPhoneNumber($recipientPhone);

        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'message' => 'Recipient phone number is required.',
            ];
        }

        $validTypes = ['image', 'document', 'audio', 'video', 'sticker'];
        $normalizedType = strtolower(trim($mediaType));

        if (!in_array($normalizedType, $validTypes, true)) {
            return [
                'success' => false,
                'message' => "Invalid media type. Allowed types: " . implode(', ', $validTypes),
            ];
        }

        if (empty($mediaUrlOrId)) {
            return [
                'success' => false,
                'message' => 'Media URL or ID is required.',
            ];
        }

        $mediaObject = is_numeric($mediaUrlOrId)
            ? ['id' => $mediaUrlOrId]
            : ['link' => $mediaUrlOrId];

        if (!empty($caption) && in_array($normalizedType, ['image', 'document', 'video'], true)) {
            $mediaObject['caption'] = $caption;
        }

        if (!empty($filename) && $normalizedType === 'document') {
            $mediaObject['filename'] = $filename;
        }

        return $this->sendMessage([
            'to' => $formattedPhone,
            'type' => $normalizedType,
            $normalizedType => $mediaObject,
        ]);
    }

    /**
     * Parse Meta error responses into human-readable messages.
     *
     * @param int $status
     * @param array $errorData
     * @param string $recipientPhone
     * @param array $payload
     * @return array
     */
    protected function parseMetaError(int $status, array $errorData, string $recipientPhone, array $payload): array
    {
        $metaErrorMessage = $errorData['error']['message'] ?? 'Meta API error';
        $metaErrorCode = $errorData['error']['code'] ?? 'UNKNOWN';

        Log::error("Meta WhatsApp API Response Error [{$status}] [Code: {$metaErrorCode}]: {$metaErrorMessage}", [
            'recipient' => $recipientPhone,
            'status' => $status,
            'response' => $errorData,
            'request_payload' => $payload,
        ]);

        $templateName = $payload['template']['name'] ?? 'template';

        $friendlyMessage = match ((int) $metaErrorCode) {
            131030 => "Recipient number (+{$recipientPhone}) is not added to the Meta Developer Sandbox 'To' list. Please add and verify this number in Meta Developer Portal -> WhatsApp -> API Setup.",
            132000 => "Template '{$templateName}' does not exist or has not been approved in your Meta WhatsApp Business Manager.",
            132001 => "Template '{$templateName}' is not available in the specified language.",
            132007 => "Template parameter mismatch for '{$templateName}'. Please check the parameter count.",
            190 => "Meta WhatsApp Access Token has expired. Please generate a new token in Meta Developer Portal.",
            default => "Meta WhatsApp API Error: {$metaErrorMessage}",
        };

        return [
            'success' => false,
            'message' => $friendlyMessage,
            'error' => $errorData,
        ];
    }

    /**
     * Fetch list of approved message templates from Meta WABA.
     *
     * @return array
     */
    public function getApprovedTemplates(): array
    {
        if (empty($this->token) || empty($this->businessAccountId)) {
            return [];
        }

        try {
            $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->businessAccountId}/message_templates";
            $response = Http::timeout(10)
                ->withToken($this->token)
                ->acceptJson()
                ->get($url, [
                    'status' => 'APPROVED',
                    'fields' => 'name,status,category,language,components',
                ]);

            if ($response->successful()) {
                return $response->json('data') ?? [];
            }
        } catch (Exception $e) {
            Log::warning("Could not fetch Meta WhatsApp templates: " . $e->getMessage());
        }

        return [];
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
