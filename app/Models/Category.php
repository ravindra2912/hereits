<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CacheManagement;

class Category extends Model
{
    use CacheManagement, SoftDeletes;

    protected $fillable = [
        'business_id',
        'parent_id',
        'name',
        'image_url',
        'type',
        'status',
        'sort_order',
        'slug',
    ];

    protected static function booted()
    {
        static::saved(function ($category) {
            if ($category->business_id) {
                $category->clearBusinessCategoryCache($category->business_id);
            }
        });

        static::deleted(function ($category) {
            if ($category->business_id) {
                $category->clearBusinessCategoryCache($category->business_id);
            }
        });
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getDisplayNameAttribute()
    {
        return $this->parent ? $this->parent->name . ' > ' . $this->name : $this->name;
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id', 'id');
    }

    public function hasLinkedItems(): bool
    {
        return $this->linkedItemsCount() > 0;
    }

    public function linkedItemsCount(): int
    {
        return match ($this->type) {
            'Products' => $this->products()->count(),
            'Services' => $this->services()->count(),
            default => 0,
        };
    }

    public function linkedItemsLabel(): string
    {
        return match ($this->type) {
            'Products' => 'product',
            'Services' => 'service',
            default => 'record',
        };
    }
}
