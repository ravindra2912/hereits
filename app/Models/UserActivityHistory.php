<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserActivityHistory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'business_id',
        'user_id',
        'type',
        'source_id',
        'title',
        'remark'
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
