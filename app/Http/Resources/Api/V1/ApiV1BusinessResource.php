<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1BusinessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'business_type' => $this->business_type,
            'business_image' => function_exists('getImage') && !empty($this->business_image) ? getImage($this->business_image) : null,
            'business_logo' => function_exists('getImage') && !empty($this->business_logo) ? getImage($this->business_logo) : null,
            'is_verified' => 1, // Default verified badge
            'rating' => number_format((float)($this->rating ?? 0), 1),
            'address' => $this->address,
            'area' => $this->area,
            'pincode' => $this->pincode,
            'contact' => $this->contact,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'business_category' => $this->whenLoaded('businessCategory', function () {
                return [
                    'id' => $this->businessCategory->id,
                    'name' => $this->businessCategory->name,
                    'image' => function_exists('getImage') && !empty($this->businessCategory->image) ? getImage($this->businessCategory->image) : null,
                ];
            }),
            'city' => $this->whenLoaded('city', function () {
                return [
                    'id' => $this->city->id,
                    'name' => $this->city->name,
                ];
            }),
            'state' => $this->whenLoaded('state', function () {
                return [
                    'id' => $this->state->id,
                    'name' => $this->state->name,
                ];
            }),
        ];
    }
}
