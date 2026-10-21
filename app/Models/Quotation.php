<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use SoftDeletes;

    const STATUS_PENDING = 'pending';
    const STATUS_INPROGRESS = 'inprogress';
    const STATUS_ORDERED = 'ordered';
    const STATUS_CANCEL = 'cancel';
    const STATUS_EXPIRED = 'expired';

    const CREATED_FROM_POS = 'pos';
    const CREATED_FROM_INQUIRY = 'inquiry';

    protected $fillable = [
        'business_id',
        'quotation_no',
        'created_by_id',
        'business_customer_id',
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
        'is_pf',
        'packing_charge',
        'tax',
        'total',
        'order_id',
        'status',
        'created_from',
        'reason',
        'valid_until',
        'notes'
    ];

    protected $casts = [
        'is_pf' => 'boolean',
        'valid_until' => 'datetime',
    ];

    public function getCustomerIdAttribute()
    {
        return $this->attributes['business_customer_id'] ?? null;
    }

    public function setCustomerIdAttribute($value)
    {
        $this->attributes['business_customer_id'] = $value;
    }

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
        return $this->belongsTo(BusinessCustomer::class, 'business_customer_id');
    }

    public function businessCustomer()
    {
        return $this->belongsTo(BusinessCustomer::class, 'business_customer_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function luckyDrawEntry()
    {
        return $this->hasOne(LuckyDrawEntry::class, 'source_id', 'order_id')
            ->where('lucky_draw_entries.for', 'order');
    }

    public function getLuckyDrawEntryDetailsAttribute()
    {
        if ($this->relationLoaded('luckyDrawEntry') && $this->luckyDrawEntry) {
            return $this->luckyDrawEntry;
        }

        if (!$this->order_id) {
            return null;
        }

        return LuckyDrawEntry::with(['luckyDraw', 'wonPrize', 'createdBy'])
            ->where('for', 'order')
            ->where('source_id', $this->order_id)
            ->first();
    }
}
