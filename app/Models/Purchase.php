<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'transaction_id',
        'coupon_id',
        'coupon_discount_amount',
        'plan_type',
        'subtotal',
        'quantity',
        'total_amount',
        'start_date',
        'end_date',
        'status',
        'plan_status',
    ];


    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transactions::class, 'transaction_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
