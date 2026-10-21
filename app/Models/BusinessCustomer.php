<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCustomer extends Model
{
    protected $fillable = [
        'business_id',
        'customer_id',
        'customer_role_id',
        'business_name',
        'customer_name',
        'contact',
        'address',
        'state',
        'city',
        'pincode',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function customerRole()
    {
        return $this->belongsTo(BusinessCustomerRole::class, 'customer_role_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'business_customer_id');
    }
}
