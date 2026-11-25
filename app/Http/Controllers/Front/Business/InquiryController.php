<?php

namespace App\Http\Controllers\Front\Business;

use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Business\StoreInquiryRequest;
use App\Models\BusinessCustomer;
use App\Services\BusinessInquiryService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InquiryController extends Controller
{
    public function __construct(
        protected BusinessInquiryService $inquiryService
    ) {}

    /**
     * Store a new storefront inquiry for a business.
     */
    public function store(string $business_slug, StoreInquiryRequest $request): JsonResponse
    {
        $business = $request->getBusiness();
        $validated = $request->validated();
        $setting = getBusinessSettings($business->id);

        try {
            return DB::transaction(function () use ($business, $validated, $setting) {
                // Collect inquiry sources
                $rawSources = [];
                if (!empty($validated['sources'])) {
                    $rawSources = $validated['sources'];
                } elseif (!empty($validated['items'])) {
                    foreach ($validated['items'] as $item) {
                        if (!empty($item['id'])) {
                            $rawSources[] = [
                                'source_id'   => (int) $item['id'],
                                'source_type' => 'product',
                            ];
                        }
                    }
                } elseif (!empty($validated['source_id'])) {
                    $rawSources[] = [
                        'source_id'   => (int) $validated['source_id'],
                        'source_type' => $validated['inquiry_type'],
                    ];
                }

                // Strictly deduplicate sources by composite key (source_type + source_id)
                $sources = collect($rawSources)
                    ->filter(fn($s) => !empty($s['source_id']) && !empty($s['source_type']))
                    ->unique(fn($s) => $s['source_type'] . '_' . (int)$s['source_id'])
                    ->values()
                    ->all();

                // Format inquiry message
                $messageParts = [];
                if (!empty($validated['subject'])) {
                    $messageParts[] = "Subject: " . $validated['subject'];
                }
                if (!empty($validated['message'])) {
                    $messageParts[] = $validated['message'];
                }
                $finalMessage = !empty($messageParts) ? implode("\n\n", $messageParts) : null;

                // Sync or record BusinessCustomer entry
                $cleanContact = preg_replace('/[^0-9]/', '', $validated['contact']) ?: $validated['contact'];
                $businessCustomer = BusinessCustomer::where('business_id', $business->id)
                    ->where(function ($q) use ($validated, $cleanContact) {
                        $q->where('contact', $validated['contact'])
                            ->orWhere('contact', $cleanContact);
                        if (auth()->check()) {
                            $q->orWhere('customer_id', auth()->id());
                        }
                    })
                    ->first();

                if (!$businessCustomer) {
                    BusinessCustomer::create([
                        'business_id'   => $business->id,
                        'customer_id'   => auth()->id(),
                        'customer_name' => $validated['name'],
                        'contact'       => $validated['contact'],
                        'email'         => $validated['email'] ?? null,
                    ]);
                }

                $remarks = 'Inquiry submitted from storefront';

                $inquiry = $this->inquiryService->createInquiry([
                    'business_id'  => $business->id,
                    'user_id'      => auth()->id(),
                    'name'         => $validated['name'],
                    'contact'      => $validated['contact'],
                    'email'        => $validated['email'] ?? null,
                    'message'      => $finalMessage,
                    'inquiry_type' => $validated['inquiry_type'],
                    'remarks'      => $remarks,
                ], $sources);

                return response()->json([
                    'status'     => 'success',
                    'message'    => 'Thank you! Your inquiry has been submitted successfully. We will get back to you shortly.',
                    'inquiry_id' => $inquiry->id,
                ]);
            });
        } catch (Exception $e) {
            Log::error('Failed to submit storefront inquiry: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Something went wrong while submitting your inquiry. Please try again.',
            ], 500);
        }
    }
}

