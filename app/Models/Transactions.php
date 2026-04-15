<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transactions extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'purchase_id',
        'payment_id',
        'order_id',
        'amount',
        'refund_amount',
        'currency',
        'payment_type',
        'payment_gateway',
        'gateway_response',
        'transaction_date',
        'refund_date',
        'payment_screen_shot',
        'status',
    ];

    protected $casts = [
        'gateway_response' => 'array',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
