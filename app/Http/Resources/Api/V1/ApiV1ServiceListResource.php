<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1ServiceListResource extends JsonResource
{
    /**
     * Transform the resource into an array with ONLY the fields used by the mobile app.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $imageUrl = null;
        if (!empty($this->image_url)) {
            if (str_starts_with($this->image_url, 'http://') || str_starts_with($this->image_url, 'https://')) {
                $imageUrl = $this->image_url;
            } elseif (file_exists(public_path('storage/' . $this->image_url))) {
                $imageUrl = asset('storage/' . $this->image_url);
            } elseif (function_exists('getImage')) {
                $resolved = getImage($this->image_url, 'service');
                $imageUrl = !empty($resolved) ? $resolved : ('https://brandbatao.com/storage/' . $this->image_url);
            } else {
                $imageUrl = 'https://brandbatao.com/storage/' . $this->image_url;
            }
        }

        return [
            'id' => (int)$this->id,
            'business_id' => (int)$this->business_id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_type' => $this->price_type,
            'price' => $this->price !== null ? (float)$this->price : null,
            'min_price' => $this->min_price !== null ? (float)$this->min_price : null,
            'max_price' => $this->max_price !== null ? (float)$this->max_price : null,
            'image_url' => $imageUrl,
            'category' => $this->relationLoaded('category') && $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'is_favorited' => function_exists('isFavorite') ? isFavorite('service', $this->id) : false,
        ];
    }
}
