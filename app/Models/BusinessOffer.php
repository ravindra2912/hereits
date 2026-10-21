<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessOffer extends Model
{
    protected $fillable = [
        'business_id',
        'type',
        'item_id',
        'is_role_wise',
        'role_wise_detail',
        'price_type',
        'value',
        'is_price_reflected_on_front',
        'is_active',
    ];

    protected $casts = [
        'is_role_wise' => 'boolean',
        'role_wise_detail' => 'array',
        'value' => 'decimal:2',
        'is_price_reflected_on_front' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'item_id');
    }
}
