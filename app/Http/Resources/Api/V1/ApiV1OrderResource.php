<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $businessImage = null;
        if ($this->business) {
            $rawImg = $this->business->business_image ?? $this->business->business_logo;
            $businessImage = function_exists('getImage') ? getImage($rawImg) : $rawImg;
        }

        $taxAmount = (float)($this->total_tax ?? ($this->tax ?? 0));

        return [
            'id' => $this->id,
            'invoice_number' => $this->invoice_number,
            'order_status' => $this->order_status ?? 'pending',
            'payment_status' => $this->payment_status ?? 'pending',
            'business_id' => $this->business_id,
            'business' => $this->business ? [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'contact' => $this->business->contact,
                'address' => $this->business->address,
                'business_image' => $businessImage,
            ] : null,
            'items' => ApiV1OrderItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->relationLoaded('items') ? $this->items->count() : 0,
            'subtotal' => (float)($this->subtotal ?? 0),
            'discount' => (float)($this->discount ?? 0),
            'tax' => $taxAmount,
            'total_tax' => $taxAmount,
            'shipping_charge' => (float)($this->shipping_charge ?? 0),
            'packing_charge' => (float)($this->packing_charge ?? 0),
            'total' => (float)($this->total ?? 0),
            'customer_name' => $this->customer_name,
            'customer_contact' => $this->customer_contact,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'pincode' => $this->pincode,
            'notes' => $this->notes,
            'user_review' => $this->whenLoaded('review', function () {
                return $this->review ? [
                    'id' => $this->review->id,
                    'rating' => (int)$this->review->rating,
                    'review' => $this->review->review,
                    'created_at' => $this->review->created_at?->toIso8601String(),
                ] : null;
            }),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
