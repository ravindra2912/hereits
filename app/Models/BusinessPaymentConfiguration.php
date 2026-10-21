<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessPaymentConfiguration extends Model
{
    protected $table = 'business_payment_configurations';

    protected $fillable = [
        'business_id',
        'payment_method',
        'upi_id',
        'razorpay_key_id',
        'razorpay_key_secret',
    ];

    protected $casts = [
        'razorpay_key_secret' => 'encrypted',
    ];

    protected $hidden = [
        'razorpay_key_secret',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }
}
