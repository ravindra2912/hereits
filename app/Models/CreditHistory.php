<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_id',
        'transaction_type',
        'type',
        'amount',
        'balance_after',
        'description',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_after' => 'float',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
