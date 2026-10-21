<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessLeaglePage extends Model
{
    protected $table = 'business_leagle_pages';

    protected $fillable = [
        'business_id',
        'type',
        'description',
        'status',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
