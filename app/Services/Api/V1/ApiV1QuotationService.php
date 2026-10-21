<?php

namespace App\Services\Api\V1;

use App\Models\Quotation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ApiV1QuotationService
{
    /**
     * Get paginated quotations for a customer.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserQuotations(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Quotation::select([
            'id',
            'business_id',
            'quotation_no',
            'business_customer_id',
            'customer_name',
            'customer_contact',
            'subtotal',
            'discount',
            'tax',
            'shipping_charge',
            'packing_charge',
            'total',
            'status',
            'created_from',
            'valid_until',
            'notes',
            'created_at',
        ])
            ->whereHas('customer', function ($q) use ($userId) {
                $q->where('customer_id', $userId);
            })
            ->with([
                'business:id,name,slug,contact,address,business_image,business_logo',
                'items:id,quotation_id,item_id,item_name,price,qty',
            ])
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get details of a single quotation for a customer.
     *
     * @param int $userId
     * @param int $quotationId
     * @return Quotation|null
     */
    public function getUserQuotationDetail(int $userId, int $quotationId): ?Quotation
    {
        return Quotation::where('id', $quotationId)
            ->whereHas('customer', function ($q) use ($userId) {
                $q->where('customer_id', $userId);
            })
            ->with([
                'business:id,name,slug,contact,address,business_image,business_logo',
                'items:id,quotation_id,item_id,item_name,price,qty',
            ])
            ->first();
    }
}
