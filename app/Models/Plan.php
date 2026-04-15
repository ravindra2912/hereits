<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'plan_type',
        'price',
        'per_product_price',
        'per_service_price',
        'max_product_limit',
        'max_service_limit',
        'duration',
        'description',
        'benefits',
        'usage_type',
        'usage_limit',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration' => 'integer',
        'usage_limit' => 'integer',
    ];
}
