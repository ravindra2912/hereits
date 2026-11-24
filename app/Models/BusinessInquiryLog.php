<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessInquiryLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'inquiry_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'remarks',
    ];

    public function inquiry()
    {
        return $this->belongsTo(BusinessInquiry::class, 'inquiry_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

