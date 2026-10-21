<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Blog extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('home_latest_blogs');
        });

        static::deleted(function () {
            Cache::forget('home_latest_blogs');
        });
    }

    protected $fillable = [
        'business_id',
        'title',
        'slug',
        'short_description',
        'content',
        'image',
        'status',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function images()
    {
        return $this->hasMany(BlogImage::class);
    }
}
