<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $fillable = [
        'business_id',
        'user_id',
        'favorite_type',
        'favorite_item_id',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'favorite_item_id', 'id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'favorite_item_id', 'id');
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(Expert::class, 'favorite_item_id', 'id');
    }
}
