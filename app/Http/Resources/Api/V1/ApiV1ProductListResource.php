<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1ProductListResource extends JsonResource
{
    /**
     * Transform the resource into an array with ONLY the fields used by the mobile app.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $firstImg = $this->relationLoaded('firstImage')
            ? $this->firstImage
            : ($this->relationLoaded('firstTwoImage') ? $this->firstTwoImage?->first() : null);

        $imageUrl = null;
        if ($firstImg && !empty($firstImg->image_url)) {
            if (($firstImg->type ?? 'image') === 'youtube_url') {
                $imageUrl = function_exists('getYoutubeThumbnail') ? getYoutubeThumbnail($firstImg->image_url) : $firstImg->image_url;
            } else {
                $imageUrl = function_exists('getImage') ? getImage($firstImg->image_url) : $firstImg->image_url;
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'price_type' => $this->price_type,
            'price' => $this->price !== null ? (float)$this->price : null,
            'sell_price' => $this->sell_price !== null ? (float)$this->sell_price : null,
            'min_price' => $this->min_price !== null ? (float)$this->min_price : null,
            'max_price' => $this->max_price !== null ? (float)$this->max_price : null,
            'image_url' => $imageUrl,
            'first_image' => $imageUrl ? ['image_url' => $imageUrl] : null,
            'category' => $this->relationLoaded('category') && $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'is_favorited' => function_exists('isFavorite') ? isFavorite('product', $this->id) : false,
        ];
    }
}
