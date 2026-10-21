<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessProductShareSetting extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'source_business_id',
        'target_business_id',
        'status',
    ];

    public function sourceBusiness()
    {
        return $this->belongsTo(Business::class, 'source_business_id');
    }

    public function targetBusiness()
    {
        return $this->belongsTo(Business::class, 'target_business_id');
    }
}
