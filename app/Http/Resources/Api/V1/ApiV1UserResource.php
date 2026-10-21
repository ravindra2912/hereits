<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1UserResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? '')),
            'email' => $this->email,
            'contact' => $this->contact ? (int) $this->contact : null,
            'dob' => $this->dob,
            'gender' => $this->gender,
            'profile' => function_exists('getImage') ? getImage($this->profile) : $this->profile,
            'role' => $this->role,
            'status' => $this->status,
            'business_id' => $this->business_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
