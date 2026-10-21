<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array with only fields used by the mobile app.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $businessData = null;
        if ($this->relationLoaded('business') && $this->business) {
            $businessData = [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'slug' => $this->business->slug,
                'rating' => (float)($this->business->rating ?? 0),
                'address' => $this->business->address,
                'full_address' => $this->business->full_address ?? $this->business->address,
                'contact' => $this->business->contact,
                'business_image' => function_exists('getImage') && !empty($this->business->business_image)
                    ? getImage($this->business->business_image)
                    : null,
                'business_logo' => function_exists('getImage') && !empty($this->business->business_logo)
                    ? getImage($this->business->business_logo)
                    : null,
                'is_following' => true,
                'is_favorited' => true,
            ];
        }

        $productData = null;
        if ($this->favorite_type === 'product' && $this->relationLoaded('product') && $this->product) {
            $firstImg = $this->product->relationLoaded('firstTwoImage')
                ? $this->product->firstTwoImage?->first()
                : ($this->product->relationLoaded('firstImage') ? $this->product->firstImage : null);

            $imgUrl = $firstImg && !empty($firstImg->image_url)
                ? (function_exists('getImage') ? getImage($firstImg->image_url) : $firstImg->image_url)
                : null;

            $productData = [
                'id' => $this->product->id,
                'name' => $this->product->name,
                'slug' => $this->product->slug,
                'description' => $this->product->description,
                'price_type' => $this->product->price_type,
                'price' => $this->product->price !== null ? (float)$this->product->price : null,
                'sell_price' => $this->product->sell_price !== null ? (float)$this->product->sell_price : null,
                'min_price' => $this->product->min_price !== null ? (float)$this->product->min_price : null,
                'max_price' => $this->product->max_price !== null ? (float)$this->product->max_price : null,
                'image_url' => $imgUrl,
                'first_image' => $imgUrl ? ['image_url' => $imgUrl] : null,
                'category' => $this->product->relationLoaded('category') && $this->product->category ? [
                    'id' => $this->product->category->id,
                    'name' => $this->product->category->name,
                ] : null,
            ];
        }

        $serviceData = null;
        if ($this->favorite_type === 'service' && $this->relationLoaded('service') && $this->service) {
            $serviceImg = function_exists('getImage') && !empty($this->service->image_url)
                ? getImage($this->service->image_url)
                : $this->service->image_url;

            $serviceData = [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'slug' => $this->service->slug,
                'description' => $this->service->description,
                'price_type' => $this->service->price_type,
                'price' => $this->service->price !== null ? (float)$this->service->price : null,
                'min_price' => $this->service->min_price !== null ? (float)$this->service->min_price : null,
                'max_price' => $this->service->max_price !== null ? (float)$this->service->max_price : null,
                'image_url' => $serviceImg,
                'category' => $this->service->relationLoaded('category') && $this->service->category ? [
                    'id' => $this->service->category->id,
                    'name' => $this->service->category->name,
                ] : null,
            ];
        }

        $expertData = null;
        if ($this->favorite_type === 'expert' && $this->relationLoaded('expert') && $this->expert) {
            $expertData = [
                'id' => $this->expert->id,
                'expert_name' => $this->expert->user?->name ?? '',
                'name' => $this->expert->user?->name ?? '',
                'title' => $this->expert->title,
                'slug' => $this->expert->slug,
                'rating' => (float)($this->expert->rating ?? 0),
                'expert_image' => function_exists('getImage') && !empty($this->expert->user?->profile)
                    ? getImage($this->expert->user->profile, 'expert')
                    : null,
                'department' => $this->expert->relationLoaded('department') && $this->expert->department ? [
                    'id' => $this->expert->department->id,
                    'name' => $this->expert->department->department_name,
                ] : null,
            ];
        }

        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'favorite_type' => $this->favorite_type,
            'favorite_item_id' => (int)$this->favorite_item_id,
            'business' => $businessData,
            'product' => $productData,
            'service' => $serviceData,
            'expert' => $expertData,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
