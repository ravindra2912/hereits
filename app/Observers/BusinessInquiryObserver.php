<?php

namespace App\Observers;

use App\Models\Business;
use App\Models\BusinessInquiry;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\Log;

class BusinessInquiryObserver
{
    /**
     * Handle the BusinessInquiry "created" event.
     */
    public function created(BusinessInquiry $inquiry): void
    {
        try {
            $business = Business::select('id', 'owner_id', 'name', 'slug')->find($inquiry->business_id);
            if (!$business) {
                return;
            }

            $typeLabel = match ($inquiry->inquiry_type) {
                BusinessInquiry::TYPE_PRODUCT => 'Product',
                BusinessInquiry::TYPE_SERVICE => 'Service',
                default => 'Contact',
            };

            $customerName = !empty($inquiry->name) ? $inquiry->name : 'A customer';
            $title = "New {$typeLabel} Inquiry Received";
            $message = "You have received a new {$typeLabel} inquiry from {$customerName}.";

            $redirectUrl = route('business.inquiries.index');

            FirebaseNotificationService::sendToBusiness(
                business: $business,
                title: $title,
                message: $message,
                redirectUrl: $redirectUrl,
                data: [
                    'event_type'   => 'new_inquiry',
                    'inquiry_id'   => (string) $inquiry->id,
                    'inquiry_type' => (string) $inquiry->inquiry_type,
                    'business_id'  => (string) $business->id,
                ]
            );
        } catch (\Throwable $e) {
            Log::error('Error sending business notification for new inquiry: ' . $e->getMessage(), [
                'inquiry_id' => $inquiry->id ?? null,
                'exception'  => $e,
            ]);
        }
    }
}
