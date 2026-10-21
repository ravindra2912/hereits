<?php

namespace App\Services\Payment;

use App\Models\BusinessPaymentConfiguration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use RuntimeException;

class RazorpayService
{
    private const BASE_URL = 'https://api.razorpay.com/v1';

    public function __construct(
        private readonly string $keyId,
        private readonly string $keySecret,
        private readonly string $context = 'platform'
    ) {
        if (empty($this->keyId) || empty($this->keySecret)) {
            throw new InvalidArgumentException("Razorpay credentials cannot be empty for context: {$this->context}");
        }
    }

    /**
     * Create an instance configured with BrandBatao platform keys (.env / config).
     * Used for Business Panel purchases (subscriptions, limits, credits).
     */
    public static function forPlatform(): self
    {
        $keyId = config('services.razorpay.key');
        $keySecret = config('services.razorpay.secret');

        if (empty($keyId) || empty($keySecret)) {
            throw new InvalidArgumentException('Platform Razorpay credentials are not configured. Please set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in .env.');
        }

        return new self($keyId, $keySecret, 'platform');
    }

    /**
     * Create an instance strictly configured with the specific business tenant's keys.
     * Used for Customer front store checkouts (orders, appointments).
     * 
     * Security Guarantee: Only the keys belonging to the passed BusinessPaymentConfiguration
     * are loaded. Keys from other businesses or platform keys are never mixed or used as fallback.
     */
    public static function forBusiness(BusinessPaymentConfiguration $config): self
    {
        if ($config->payment_method !== 'razorpay') {
            throw new InvalidArgumentException("Business #{$config->business_id} does not have Razorpay active as payment method.");
        }

        $keyId = $config->razorpay_key_id;
        $keySecret = $config->razorpay_key_secret; // Decrypted automatically by Eloquent cast

        if (empty($keyId) || empty($keySecret)) {
            throw new InvalidArgumentException("Razorpay credentials are incomplete for Business #{$config->business_id}. Key ID and Secret are required.");
        }

        return new self($keyId, $keySecret, "business:{$config->business_id}");
    }

    /**
     * Get the public Key ID for client-side checkout modal.
     */
    public function getKeyId(): string
    {
        return $this->keyId;
    }

    /**
     * Create a Razorpay Order.
     *
     * @param float $amount Amount in INR (will be converted to paise)
     * @param string $receiptId Unique internal receipt/order identifier
     * @param array $notes Optional key-value notes (max 15 pairs)
     * @return array
     */
    public function createOrder(float $amount, string $receiptId, array $notes = []): array
    {
        $amountInPaise = (int) round($amount * 100);

        if ($amountInPaise < 100) {
            throw new InvalidArgumentException('Order amount must be at least ₹1.00');
        }

        $payload = [
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => substr($receiptId, 0, 40),
            'notes' => array_slice($notes, 0, 15),
        ];

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(20)
                ->post(self::BASE_URL . '/orders', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'order_id' => $data['id'],
                    'amount' => $data['amount'],
                    'currency' => $data['currency'],
                    'status' => $data['status'],
                    'raw' => $data,
                ];
            }

            if ($response->status() === 401) {
                Log::error("Razorpay authentication failed [{$this->context}]: Invalid Key ID or Key Secret");
                throw new \Symfony\Component\HttpKernel\Exception\HttpException(401, 'Razorpay authentication failed: Invalid Key ID or Secret.');
            }

            $error = $response->json()['error'] ?? [];
            $errorMessage = $error['description'] ?? $response->body();

            Log::error("Razorpay order creation failed [{$this->context}]: " . $errorMessage, [
                'status' => $response->status(),
                'payload' => $payload,
            ]);

            throw new RuntimeException("Payment Gateway Error: " . ($error['description'] ?? 'Unable to initiate payment with Razorpay.'));
        } catch (\Exception $e) {
            if ($e instanceof RuntimeException || $e instanceof InvalidArgumentException || $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                throw $e;
            }
            Log::error("Razorpay order HTTP error [{$this->context}]: " . $e->getMessage());
            throw new RuntimeException('Unable to communicate with Razorpay. Please try again later.');
        }
    }

    /**
     * Verify payment signature securely using HMAC-SHA256.
     * Compares computed signature with received signature using timing-safe hash_equals.
     *
     * @param string $razorpayOrderId
     * @param string $razorpayPaymentId
     * @param string $razorpaySignature
     * @return bool
     */
    public function verifySignature(string $razorpayOrderId, string $razorpayPaymentId, string $razorpaySignature): bool
    {
        if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, $this->keySecret);

        return hash_equals($expectedSignature, $razorpaySignature);
    }

    /**
     * Fetch payment details from Razorpay to verify status.
     *
     * @param string $paymentId
     * @return array
     */
    public function fetchPayment(string $paymentId): array
    {
        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->timeout(20)
            ->get(self::BASE_URL . "/payments/{$paymentId}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error("Razorpay fetch payment failed [{$this->context}]: " . $response->body());
        throw new RuntimeException('Failed to fetch payment details from Razorpay.');
    }
}
