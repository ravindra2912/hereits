<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'business_id',
        'image_url',
        'status',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
