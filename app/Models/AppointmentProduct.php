<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AppointmentProduct extends Model
{
    protected $table = 'appointment_products';

    protected $fillable = [
        'business_id',
        'appointment_id',
        'product_id',
        'product_name',
        'product_price',
        'qty',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(AppointmentBooking::class, 'appointment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
