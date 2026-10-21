<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::saved(function ($order) {
            if ($order->customer_id) {
                \App\Models\BusinessCustomer::firstOrCreate(
                    [
                        'business_id' => $order->business_id,
                        'customer_id' => $order->customer_id
                    ],
                    [
                        'customer_name' => $order->customer_name,
                        'contact' => $order->customer_contact,
                        'address' => $order->address,
                        'state' => $order->state,
                        'city' => $order->city,
                        'pincode' => $order->pincode,
                    ]
                );
            }
        });
    }

    protected $fillable = [
        'business_id',
        'transaction_id',
        'invoice_number',
        'created_user_id',
        'assign_to',
        'order_source',
        'order_type',
        'customer_id',
        'business_customer_id',
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
        'is_pf',
        'packing_charge',
        'total',
        'notes',
        'payment_status',
        'order_status'
    ];

    protected $casts = [
        'is_pf' => 'boolean',
        'packing_charge' => 'decimal:2',
        'shipping_charge' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
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
        return $this->hasMany(UserActivityHistory::class, 'source_id')->where('type', 'order');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function businessCustomer()
    {
        return $this->belongsTo(BusinessCustomer::class, 'business_customer_id');
    }

    public function transaction()
    {
        return $this->belongsTo(BusinessTransactions::class, 'transaction_id');
    }

    public function transactions()
    {
        return $this->hasMany(BusinessTransactions::class, 'source_id')->where('type', 'order');
    }

    public function luckyDrawEntry()
    {
        return $this->hasOne(LuckyDrawEntry::class, 'source_id')->where('lucky_draw_entries.for', 'order');
    }
}
