<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'invoice_number',
        'created_user_id',
        'order_source',
        'order_type',
        'customer_id',
        'customer_name',
        'customer_contact',
        'address',
        'state',
        'city',
        'pincode',
        'igst',
        'sgst',
        'cgst',
        'total_tax',
        'subtotal',
        'discount',
        'shipping_charge',
        'total',
        'notes',
        'payment_method',
        'payment_status',
        'order_status'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function history()
    {
        return $this->hasMany(OrderHistory::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
