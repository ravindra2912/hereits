<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessInquirySource extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'source_id',
        'source_type',
    ];

    public function inquiry()
    {
        return $this->belongsTo(BusinessInquiry::class, 'inquiry_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'source_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class, 'source_id');
    }

    /**
     * Get associated item (Product or Service).
     */
    public function getItemAttribute()
    {
        if ($this->source_type === 'product') {
            return $this->product;
        }
        if ($this->source_type === 'service') {
            return $this->service;
        }
        return null;
    }
}

