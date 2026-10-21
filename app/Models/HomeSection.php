<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeSection extends Model
{
    protected $fillable = [
        'business_id',
        'section_type',
        'title',
        'image_count',
        'ui_style',
        'item_selection_type',
        'sort_order',
        'status',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function items()
    {
        return $this->hasMany(HomeSectionItem::class, 'home_section_id')->orderBy('sort_order', 'asc');
    }
}
