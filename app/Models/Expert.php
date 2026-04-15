<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Expert extends Authenticatable
{
    use SoftDeletes, Notifiable;

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'is_appointment_book_with_time_slot' => 'boolean',
        'is_need_booking_confirmation' => 'boolean',
        'is_default' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(AppointmentDepartment::class, 'department_id', 'id')->select('id', 'department_name', 'department_image');
    }

    public function businessSetting()
    {
        return $this->belongsTo(BusinessSetting::class, 'business_id', 'business_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(ReviewAndRating::class, 'review_on_id', 'id')->where('review_type', 'expert');
    }

    public function timings()
    {
        return $this->hasMany(BusinessTiming::class, 'expert_id', 'id');
    }
}
