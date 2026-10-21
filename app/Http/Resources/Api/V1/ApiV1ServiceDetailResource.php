<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1ServiceDetailResource extends JsonResource
{
    /**
     * Additional data to be injected (e.g., recommended services, business, is_favorited).
     */
    protected mixed $businessData;
    protected mixed $recommendedServicesData;
    protected bool $isFavorited;

    public function __construct($resource, $businessData = null, $recommendedServicesData = [], bool $isFavorited = false)
    {
        parent::__construct($resource);
        $this->businessData = $businessData;
        $this->recommendedServicesData = $recommendedServicesData;
        $this->isFavorited = $isFavorited;
    }

    /**
     * Transform the service detail into an array formatted for the mobile app and API consumers.
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
                $imageUrl = !empty($resolved) ? $resolved : ('https://hereits.com/storage/' . $this->image_url);
            } else {
                $imageUrl = 'https://hereits.com/storage/' . $this->image_url;
            }
        }

        $serviceData = [
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
            'is_favorited' => $this->isFavorited,
            'category' => $this->relationLoaded('category') && $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
        ];

        $businessInfo = null;
        $businessObj = $this->businessData ?? ($this->relationLoaded('business') ? $this->business : null);
        if ($businessObj) {
            $businessInfo = [
                'id' => (int)$businessObj->id,
                'name' => $businessObj->name,
                'rating' => (float)($businessObj->rating ?? 4.5),
                'address' => $businessObj->address,
                'contact' => $businessObj->contact,
                'business_image' => !empty($businessObj->business_image) && function_exists('getImage')
                    ? getImage($businessObj->business_image)
                    : null,
            ];
        }

        $recommendedServicesCollection = ApiV1ServiceListResource::collection($this->recommendedServicesData);

        return [
            'service' => $serviceData,
            'business' => $businessInfo,
            'recommendedServices' => $recommendedServicesCollection,
            'recommended_services' => $recommendedServicesCollection,
            'is_favorited' => $this->isFavorited,
        ];
    }
}
