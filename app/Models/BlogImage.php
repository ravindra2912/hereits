<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'business_id',
        'blog_id',
        'image',
        'original_name',
        'file_size',
        'mime_type',
    ];

    /**
     * Get the blog that owns the image.
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Get the business that owns the image.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the full URL for the image.
     */
    public function getImageUrlAttribute(): string
    {
        return getImage($this->image);
    }
}
