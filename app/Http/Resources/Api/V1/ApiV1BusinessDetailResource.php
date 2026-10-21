<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1BusinessDetailResource extends JsonResource
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
            'rating' => number_format((float)($this->rating ?? 0), 1),
            'address' => $this->address,
            'full_address' => $this->full_address,
            'area' => $this->area,
            'pincode' => $this->pincode,
            'contact' => $this->contact,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'seo_description' => $this->seo_description,
            'seo_keyword' => $this->seo_keyword,
            'facebook' => $this->facebook,
            'instagram' => $this->instagram,
            'twitter' => $this->twitter,
            'linkedin' => $this->linkedin,
            'youtube' => $this->youtube,
            'is_open' => function_exists('isBusinessOpen') ? isBusinessOpen($this->id) : false,
            'is_verified' => 1,
            'is_favorited' => function_exists('isFavorite') ? isFavorite('business', $this->id) : false,
            'is_following' => function_exists('isFavorite') ? isFavorite('business', $this->id) : false,
            'business_category' => $this->whenLoaded('businessCategory', function () {
                return [
                    'id' => $this->businessCategory->id,
                    'name' => $this->businessCategory->name,
                    'image' => function_exists('getImage') && !empty($this->businessCategory->image) ? getImage($this->businessCategory->image) : null,
                ];
            }),
            'country' => $this->whenLoaded('country', function () {
                return [
                    'id' => $this->country->id,
                    'name' => $this->country->name,
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
