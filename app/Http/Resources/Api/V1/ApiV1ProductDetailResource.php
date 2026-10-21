<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1ProductDetailResource extends JsonResource
{
    /**
     * Additional data to be injected (e.g., related products, business, is_favorited).
     */
    protected mixed $businessData;
    protected mixed $relatedProductsData;
    protected bool $isFavorited;

    public function __construct($resource, $businessData = null, $relatedProductsData = [], bool $isFavorited = false)
    {
        parent::__construct($resource);
        $this->businessData = $businessData;
        $this->relatedProductsData = $relatedProductsData;
        $this->isFavorited = $isFavorited;
    }

    /**
     * Transform the product detail into an array with ONLY fields used by the mobile app.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $images = [];
        if ($this->relationLoaded('images')) {
            $images = $this->images->map(function ($img) {
                $isYoutube = ($img->type ?? 'image') === 'youtube_url';
                $thumb = $isYoutube
                    ? (function_exists('getYoutubeThumbnail') ? getYoutubeThumbnail($img->image_url) : $img->image_url)
                    : (function_exists('getImage') && !empty($img->image_url) ? getImage($img->image_url) : $img->image_url);

                return [
                    'id' => $img->id,
                    'type' => $img->type ?? 'image',
                    'image_url' => $thumb,
                    'video_url' => $isYoutube ? $img->image_url : null,
                ];
            })->values()->all();
        }

        $productData = [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price_type' => $this->price_type,
            'price' => $this->price !== null ? (float)$this->price : null,
            'sell_price' => $this->sell_price !== null ? (float)$this->sell_price : null,
            'min_price' => $this->min_price !== null ? (float)$this->min_price : null,
            'max_price' => $this->max_price !== null ? (float)$this->max_price : null,
            'is_favorited' => $this->isFavorited,
            'category' => $this->relationLoaded('category') && $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'images' => $images,
        ];

        $businessInfo = null;
        $businessObj = $this->businessData ?? ($this->relationLoaded('business') ? $this->business : null);
        if ($businessObj) {
            $businessInfo = [
                'id' => $businessObj->id,
                'name' => $businessObj->name,
                'rating' => (float)($businessObj->rating ?? 0),
                'address' => $businessObj->address,
                'contact' => $businessObj->contact,
            ];
        }

        $relatedProductsCollection = ApiV1ProductListResource::collection($this->relatedProductsData);

        return [
            'product' => $productData,
            'business' => $businessInfo,
            'relatedProducts' => $relatedProductsCollection,
            'related_products' => $relatedProductsCollection,
        ];
    }
}
