<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    
    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }
}
