<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppointmentBooking extends Model
{
    use SoftDeletes;

    protected static function booted()
    {
        static::saved(function ($appointment) {
            if ($appointment->user_id) {
                \App\Models\BusinessCustomer::firstOrCreate(
                    [
                        'business_id' => $appointment->business_id,
                        'customer_id' => $appointment->user_id
                    ],
                    [
                        'customer_name' => $appointment->user_name,
                        'contact' => $appointment->user_contact,
                    ]
                );
            }
        });
    }

    public function department()
    {
        return $this->belongsTo(AppointmentDepartment::class, 'department_id', 'id')->select('id', 'department_name', 'department_image');
    }

    public function expert()
    {
        return $this->belongsTo(Expert::class, 'expert_id', 'id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    public function review()
    {
        return $this->hasOne(ReviewAndRating::class, 'id', 'review_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function appointmentProducts()
    {
        return $this->hasMany(AppointmentProduct::class, 'appointment_id', 'id');
    }
}
