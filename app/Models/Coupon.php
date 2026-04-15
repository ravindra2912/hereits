<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code',
        'Influencer_business_id',
        'discount_type',
        'discount_value',
        'applicable_for',
        'max_discount',
        'min_purchase',
        'usage_type',
        'usage_limit',
        'is_limit_per_business',
        'usage_limit_per_business',
        'usage_count',
        'is_for_specific_business',
        'business_ids',
        'start_date',
        'end_date',
        'status',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'applicable_for' => 'array',
        'is_for_specific_business' => 'boolean',
        'business_ids' => 'array',
        'is_limit_per_business' => 'boolean',
    ];

    protected $attributes = [
        'applicable_for' => '["all"]',
        'is_for_specific_business' => false,
        'business_ids' => '[]',
        'is_limit_per_business' => false,
        'usage_limit_per_business' => 1,
    ];

    public function influencerBusiness()
    {
        return $this->belongsTo(Business::class, 'Influencer_business_id', 'id');
    }

    public function businesses()
    {
        // This is a workaround since we store IDs in a JSON array. 
        // For a true relationship we would need a pivot table.
        // But for retrieval we can use whereIn.
        if (empty($this->business_ids)) {
            return collect([]);
        }
        return Business::whereIn('id', $this->business_ids)->get();
    }
}
