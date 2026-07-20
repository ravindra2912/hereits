<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'quotation_no',
        'created_by_id',
        'customer_id',
        'customer_name',
        'customer_contact',
        'address',
        'state',
        'city',
        'pincode',
        'subtotal',
        'discount',
        'shipping_charge',
        'tax',
        'total',
        'order_id',
        'status',
        'reason',
        'valid_until',
        'notes'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }
}
