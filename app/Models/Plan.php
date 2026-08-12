<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'plan_type',
        'price',
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
