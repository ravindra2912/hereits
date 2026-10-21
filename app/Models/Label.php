<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'image',
        'short_order',
        'status',
        'slug',
    ];

    public function products()
    {
        return Product::query()->whereJsonContains('label_id', $this->id);
    }

    public function hasLinkedProducts(): bool
    {
        return $this->products()->exists();
    }

    public function linkedProductsCount(): int
    {
        return $this->products()->count();
    }
}
