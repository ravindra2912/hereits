<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessProductShareSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'business_product_share_settings';

    protected $fillable = [
        'source_business_id',
        'target_business_id',
        'status',
    ];

    /**
     * Source Business that shares products
     */
    public function sourceBusiness(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'source_business_id', 'id');
    }

    /**
     * Target Business that receives/displays shared products
     */
    public function targetBusiness(): BelongsTo
    {
        return $this->belongsTo(Business::class, 'target_business_id', 'id');
    }
}
