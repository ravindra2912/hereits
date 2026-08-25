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

    public function sharedProductSettings()
    {
        return $this->hasMany(BusinessProductShareSetting::class, 'source_business_id', 'id');
    }

    public function receivedProductSettings()
    {
        return $this->hasMany(BusinessProductShareSetting::class, 'target_business_id', 'id');
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

    public function analyticsEvents()
    {
        return $this->hasMany(BusinessAnalyticsEvent::class, 'business_id', 'id');
    }

    // ***************************
    // for dashboard calculation end
    // ***************************

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'favorite_item_id', 'id')->where('favorite_type', 'business');
    }

    public function chatParticipants()
    {
        return $this->morphMany(ChatConversationParticipant::class, 'participant');
    }

    public function chatMessages()
    {
        return $this->morphMany(ChatMessage::class, 'sender');
    }

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->area,
            $this->city?->name,
            $this->state?->name,
            $this->country?->name,
        ], fn ($value) => filled($value));

        $address = implode(', ', $parts);

        if (filled($this->pincode)) {
            $address .= $address !== '' ? ' - ' . $this->pincode : $this->pincode;
        }

        return $address;
    }

    /**
     * Scope a query to filter businesses by distance.
     */
    public function scopeNearby($query, $latitude, $longitude, $radius = 5)
    {
        $haversine = "(6371 * acos(cos(radians($latitude)) * cos(radians(latitude)) * cos(radians(longitude) - radians($longitude)) + sin(radians($latitude)) * sin(radians(latitude))))";

        return $query->addSelect(\Illuminate\Support\Facades\DB::raw("$haversine AS distance"))
            ->whereRaw("$haversine <= ?", [$radius])
            ->orderBy('distance');
    }



    /**
     * Scope a query to filter businesses within a bounding box.
     */
    public function scopeInBoundaries($query, $swLat, $swLng, $neLat, $neLng)
    {
        return $query->whereBetween('latitude', [$swLat, $neLat])
            ->whereBetween('longitude', [$swLng, $neLng]);
    }
}
