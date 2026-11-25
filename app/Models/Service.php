<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'slug',
        'image_url',
        'description',
        'price_type',
        'price',
        'min_price',
        'max_price',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function businessCategory()
    {
        return $this->hasOneThrough(
            BusinessCategory::class,
            Category::class,
            'id',
            'id',
            'category_id',
            'business_category_id'
        );
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
