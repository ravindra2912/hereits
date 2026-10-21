<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'business_id',
        'parent_product_id',
        'parent_product_business_id',
        'category_id',
        'label_id',
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
        'length',
        'width',
        'height',
        'weight',
        'status',
        'share_type',
    ];

    protected $casts = [
        'label_id' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Returns a Builder for labels matching the stored JSON IDs.
     * Usage: $product->labels()->get()
     */
    public function labels()
    {
        $ids = $this->label_id ?? [];
        return Label::select('id', 'name', 'slug')->whereIn('id', $ids);
    }

    /**
     * Get labels as a collection (non-relation accessor).
     */
    public function getLabelsListAttribute()
    {
        $ids = $this->label_id ?? [];
        return empty($ids) ? collect() : Label::select('id', 'name', 'slug')->whereIn('id', $ids)->get();
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

    public function firstTwoImage()
    {
        return $this->hasMany(ProductImage::class)->where('type', 'image')->orderBy('sort_order', 'asc')->take(2);
    }
}
