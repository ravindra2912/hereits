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
    protected ?string $templateName;
    protected string $templateLang;
    protected ?string $buttonType;

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

        $this->templateName = config('services.whatsapp.template_name');
        $this->templateLang = config('services.whatsapp.template_lang', 'en_US');
        $this->buttonType = config('services.whatsapp.template_button_type');
    }

    /**
     * Send OTP to a WhatsApp phone number using Meta Cloud API.
     *
     * @param string $recipientPhone
     * @param string $otp
     * @param string|null $templateName
     * @param string|null $language
     * @param array|null $customComponents
     * @return array ['success' => bool, 'message' => string, 'data' => mixed]
     */
    public function sendOtp(
        string $recipientPhone,
        string $otp,
        ?string $templateName = null,
        ?string $language = null,
        ?array $customComponents = null
    ): array {
        $formattedPhone = $this->formatPhoneNumber($recipientPhone);

        // Check if Meta credentials are configured
        if (empty($this->token) || empty($this->phoneNumberId)) {
            Log::warning("Meta WhatsApp Cloud API credentials not configured. OTP for {$formattedPhone} is: {$otp}");

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

        // Resolve template name and language
        $activeTemplate = $templateName ?: $this->templateName;
        $activeLang = $language ?: $this->templateLang ?: 'en_US';

        if (empty($activeTemplate)) {
            return [
                'success' => false,
                'message' => 'WhatsApp template name is required by Meta Cloud API. Please provide a template name or configure META_WHATSAPP_OTP_TEMPLATE in .env.',
            ];
        }

        $url = "https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages";

        try {
            $templateData = [
                'name' => $activeTemplate,
                'language' => [
                    'code' => $activeLang,
                ],
            ];

            // If custom components provided, use them directly
            if (!empty($customComponents)) {
                $templateData['components'] = $customComponents;
            } elseif (strtolower($activeTemplate) === 'hello_world') {
                // Meta's hello_world template has no components/parameters
                $templateData['components'] = [];
            } else {
                // Build components for custom OTP template
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

                $resolvedButtonType = $this->buttonType;
                if (empty($resolvedButtonType) && $activeTemplate === 'hereits_otp') {
                    $resolvedButtonType = 'url';
                }

                if ($resolvedButtonType === 'copy_code') {
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
                } elseif ($resolvedButtonType === 'url') {
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

                $templateData['components'] = $components;
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'recipient_type' => 'individual',
                'to' => $formattedPhone,
                'type' => 'template',
                'template' => $templateData,
            ];

            // Log outgoing Meta WhatsApp API Request
            Log::info("Meta WhatsApp API Request", [
                'endpoint' => $url,
                'recipient' => $formattedPhone,
                'template_name' => $activeTemplate,
                'language' => $activeLang,
                'payload' => $payload,
            ]);

            $response = Http::timeout(15)
                ->withToken($this->token)
                ->acceptJson()
                ->post($url, $payload);

            // Handle parameter mismatch or button mismatch auto-retries
            if (!$response->successful()) {
                $err = $response->json();
                $errMessage = $err['error']['message'] ?? '';
                $errDetails = $err['error']['error_data']['details'] ?? '';
                $errCode = $err['error']['code'] ?? 0;
                $allErrText = $errMessage . ' ' . $errDetails;

                // Case 1: Template failed because button parameter was sent but template has no button
                if (isset($templateData['components']) && count($templateData['components']) > 1 &&
                    (stripos($allErrText, 'does not match') !== false || stripos($allErrText, 'invalid button') !== false || $errCode == 132007)) {
                    Log::warning("Retrying Meta WhatsApp template with body component only...", [
                        'template' => $activeTemplate,
                        'reason' => $allErrText,
                    ]);

                    $templateData['components'] = [
                        [
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $otp],
                            ],
                        ],
                    ];
                    $payload['template'] = $templateData;

                    $response = Http::timeout(15)
                        ->withToken($this->token)
                        ->acceptJson()
                        ->post($url, $payload);
                }
                // Case 2: Template failed because template expects a button parameter but we sent body only
                elseif (isset($templateData['components']) && count($templateData['components']) === 1 &&
                    ($errCode == 131008 || stripos($allErrText, 'button') !== false || stripos($allErrText, 'requires a parameter') !== false)) {

                    $isUrlButton = stripos($allErrText, 'url') !== false;
                    $retryButtonComponent = $isUrlButton
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

                    Log::warning("Retrying Meta WhatsApp template with required button component...", [
                        'template' => $activeTemplate,
                        'button_type' => $isUrlButton ? 'url' : 'copy_code',
                        'reason' => $allErrText,
                    ]);

                    $templateData['components'][] = $retryButtonComponent;
                    $payload['template'] = $templateData;

                    $response = Http::timeout(15)
                        ->withToken($this->token)
                        ->acceptJson()
                        ->post($url, $payload);
                }
            }

            $status = $response->status();
            $responseBody = $response->json() ?? $response->body();

            // Log incoming Meta WhatsApp API Response
            if ($response->successful()) {
                Log::info("Meta WhatsApp API Response [{$status} OK]", [
                    'recipient' => $formattedPhone,
                    'status' => $status,
                    'template' => $activeTemplate,
                    'response' => $responseBody,
                ]);

                return [
                    'success' => true,
                    'message' => 'OTP sent successfully via WhatsApp.',
                    'data' => $responseBody,
                ];
            }

            $errorData = is_array($responseBody) ? $responseBody : ['raw' => $responseBody];
            $metaErrorMessage = $errorData['error']['message'] ?? 'Meta API error';
            $metaErrorCode = $errorData['error']['code'] ?? 'UNKNOWN';

            Log::error("Meta WhatsApp API Response Error [{$status}] [Code: {$metaErrorCode}]: {$metaErrorMessage}", [
                'recipient' => $formattedPhone,
                'status' => $status,
                'template' => $activeTemplate,
                'response' => $errorData,
                'request_payload' => $payload,
            ]);

            // Format descriptive error message based on Meta API error codes
            $friendlyMessage = match ((int) $metaErrorCode) {
                131030 => "Recipient number (+{$formattedPhone}) is not added to the Meta Developer Sandbox 'To' list. Please add and verify this number in Meta Developer Portal -> WhatsApp -> API Setup.",
                132000 => "Template '{$activeTemplate}' does not exist or has not been approved in your Meta WhatsApp Business Manager.",
                132001 => "Template '{$activeTemplate}' is not available in language '{$activeLang}'.",
                132007 => "Template parameter mismatch for '{$activeTemplate}'. Please check the parameter count.",
                190 => "Meta WhatsApp Access Token has expired. Please generate a new token in Meta Developer Portal.",
                default => "Meta WhatsApp API Error: {$metaErrorMessage}",
            };

            return [
                'success' => false,
                'message' => $friendlyMessage,
                'error' => $errorData,
            ];

        } catch (Exception $e) {
            Log::error("Meta WhatsApp API Exception: " . $e->getMessage(), [
                'recipient' => $formattedPhone,
                'endpoint' => $url,
                'template' => $activeTemplate ?? null,
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
