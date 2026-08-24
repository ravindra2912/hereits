<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'deduct_credit_per_customer_appointment',
        'deduct_credit_per_self_appointment',
        'deduct_credit_per_customer_order',
        'deduct_credit_per_self_order',
        'status',
    ];
    public function businesses()
    {
        return $this->hasMany(Business::class, 'business_category_id', 'id');
    }
}
