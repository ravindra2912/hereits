<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'business_id',
        'category_id',
        'name',
        'sku',
        'slug',
        'description',
        'price_type',
        'price',
        'sell_price',
        'min_price',
        'max_price',
        'quantity',
        'status',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function firstImage()
    {
        return $this->hasOne(ProductImage::class)->where('type', 'image')->orderBy('sort_order');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'favorite_item_id', 'id')->where('favorite_type', 'product');
    }
}
