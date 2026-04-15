<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id', 'id')->select('id', 'first_name', 'last_name', 'email', 'contact');
    }

    public function businessCategory()
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id', 'id')->select('id', 'name', 'image');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id')->select('id', 'sortname', 'name', 'phonecode');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id', 'id')->select('id', 'name', 'country_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id', 'id')->select('id', 'name', 'state_id');
    }

    public function reviews()
    {
        return $this->hasMany(ReviewAndRating::class, 'business_id', 'id')->where('review_type', 'business');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'business_id', 'id');
    }

    public function businessSetting()
    {
        return $this->hasOne(BusinessSetting::class, 'business_id', 'id');
    }

    public function influencerCoupon()
    {
        return $this->hasOne(Coupon::class, 'Influencer_business_id', 'id');
    }

    // ***************************
    // for dashboard calculation start
    // ***************************

    public function products()
    {
        return $this->hasMany(Product::class, 'business_id', 'id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'business_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany(AppointmentBooking::class, 'business_id', 'id');
    }

    public function experts()
    {
        return $this->hasMany(Expert::class, 'business_id', 'id');
    }

    public function appointmentDepartments()
    {
        return $this->hasMany(AppointmentDepartment::class, 'business_id', 'id');
    }

    // ***************************
    // for dashboard calculation end
    // ***************************



    public function hasActivePlan($type = 'subscription')
    {
        return $this->purchases()
            ->where('plan_type', $type)
            ->where('status', 'paid')
            ->where('plan_status', 'active')
            ->where('end_date', '>', now())
            ->exists();
    }
}
