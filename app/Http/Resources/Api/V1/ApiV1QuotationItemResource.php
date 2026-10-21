<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiV1QuotationItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $price = (float)($this->price ?? 0);
        $qty = (int)($this->qty ?? 1);

        return [
            'id' => $this->id,
            'quotation_id' => $this->quotation_id,
            'item_id' => $this->item_id,
            'item_name' => $this->item_name,
            'price' => $price,
            'qty' => $qty,
            'quantity' => $qty,
            'total' => round($price * $qty, 2),
        ];
    }
}
