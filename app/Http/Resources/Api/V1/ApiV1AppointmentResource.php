<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $businessImage = null;
        if ($this->business) {
            $rawImg = $this->business->business_image ?? $this->business->business_logo;
            $businessImage = function_exists('getImage') ? getImage($rawImg) : $rawImg;
        }

        $expertImage = null;
        if ($this->expert) {
            $rawExpImg = $this->expert->user?->profile;
            $expertImage = function_exists('getImage') && !empty($rawExpImg) ? getImage($rawExpImg, 'expert') : $rawExpImg;
        }

        return [
            'id' => $this->id,
            'business_id' => $this->business_id,
            'expert_id' => $this->expert_id,
            'token_number' => $this->token_number,
            'booking_date' => $this->booking_date,
            'slot_start_time' => $this->slot_start_time,
            'slot_end_time' => $this->slot_end_time,
            'status' => $this->status ?? 'pending',
            'user_name' => $this->user_name,
            'user_contact' => $this->user_contact,
            'appointment_for' => $this->appointment_for ?? 'self',
            'note' => $this->note,
            'business' => $this->business ? [
                'id' => $this->business->id,
                'name' => $this->business->name,
                'address' => $this->business->address,
                'contact' => $this->business->contact,
                'business_image' => $businessImage,
            ] : null,
            'expert' => $this->expert ? [
                'id' => $this->expert->id,
                'expert_name' => $this->expert->user?->name ?? '',
                'title' => $this->expert->title,
                'expert_image' => $expertImage,
            ] : null,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
