<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array with ONLY the fields used by the mobile app.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'image_url' => function_exists('getImage') && !empty($this->image_url)
                ? getImage($this->image_url)
                : $this->image_url,
        ];
    }
}
