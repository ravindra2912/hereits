<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewAndRating extends Model
{
    protected $fillable = [
        'user_id',
        'business_id',
        'review_type',
        'rating',
        'review',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }
}
