<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'image_url',
        'type',
        'status',
        'sort_order',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'category_id', 'id');
    }
}
