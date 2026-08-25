<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'business_id',
        'parent_product_id',
        'parent_product_business_id',
        'share_type',
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

    public function parentProduct()
    {
        return $this->belongsTo(Product::class, 'parent_product_id', 'id');
    }

    public function parentProductBusiness()
    {
        return $this->belongsTo(Business::class, 'parent_product_business_id', 'id');
    }

    public function sharedCopies()
    {
        return $this->hasMany(Product::class, 'parent_product_id', 'id');
    }

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

    public function firstTwoImages()
    {
        return $this->hasMany(ProductImage::class)->where('type', 'image')->orderBy('sort_order')->take(2);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'favorite_item_id', 'id')->where('favorite_type', 'product');
    }
}
