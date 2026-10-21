<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1QuotationResource extends JsonResource
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

        return [
            'id' => $this->id,
            'quotation_no' => $this->quotation_no,
            'status' => $this->status ?? 'pending',
            'created_from' => $this->created_from ?? 'pos',
            'business_id' => $this->business_id,
            'business' => $this->business ? [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'slug' => $this->business->slug,
                'contact' => $this->business->contact,
                'address' => $this->business->address,
                'business_image' => $businessImage,
            ] : null,
            'items' => ApiV1QuotationItemResource::collection($this->whenLoaded('items')),
            'items_count' => $this->relationLoaded('items') ? $this->items->count() : 0,
            'subtotal' => (float)($this->subtotal ?? 0),
            'discount' => (float)($this->discount ?? 0),
            'tax' => (float)($this->tax ?? 0),
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
            'valid_until' => $this->valid_until ? $this->valid_until->format('Y-m-d') : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
