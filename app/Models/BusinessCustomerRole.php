<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCustomerRole extends Model
{
    protected $fillable = [
        'business_id',
        'role_name',
        'status',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function businessCustomers()
    {
        return $this->hasMany(BusinessCustomer::class, 'customer_role_id');
    }
}
