<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSectionItem extends Model
{
    protected $fillable = [
        'home_section_id',
        'category_id',
        'product_id',
        'service_id',
        'label_id',
        'expert_id',
        'gallery_id',
        'image_path',
        'redirect_url',
        'status',
        'sort_order',
    ];

    public function homeSection()
    {
        return $this->belongsTo(HomeSection::class, 'home_section_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function label()
    {
        return $this->belongsTo(Label::class, 'label_id');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class, 'expert_id');
    }

    public function gallery()
    {
        return $this->belongsTo(Gallery::class, 'gallery_id');
    }
}
