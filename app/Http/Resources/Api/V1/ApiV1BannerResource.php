<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1BannerResource extends JsonResource
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
            'title' => $this->title ?? 'Featured',
            'image' => function_exists('getImage') ? getImage($this->image_url ?? $this->image) : ($this->image_url ?? $this->image),
            'image_url' => function_exists('getImage') ? getImage($this->image_url ?? $this->image) : ($this->image_url ?? $this->image),
            'business_id' => $this->business_id,
            'url' => $this->url ?? null,
        ];
    }
}
