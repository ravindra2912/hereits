<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessTransactions extends Model
{
    use SoftDeletes;

    protected $table = 'business_transactions';

    protected $fillable = [
        'business_id',
        'type',
        'transaction_id',
        'transaction_screenshot',
        'source_id',
        'amount',
        'refund_amount',
        'currency',
        'payment_type',
        'payment_gateway',
        'gateway_response',
        'transaction_date',
        'refund_date',
        'status',
    ];

    protected $casts = [
        'gateway_response' => 'array',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'source_id');
    }
}
