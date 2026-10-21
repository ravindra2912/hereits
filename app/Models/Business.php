<?php

namespace App\Models;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Business extends Model
{
    use SoftDeletes;

    protected $guarded = [
        'id',
        'owner_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('home_top_rated_businesses');
        });

        static::deleted(function () {
            Cache::forget('home_top_rated_businesses');
        });
    }

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

    public function leaglePages()
    {
        return $this->hasMany(BusinessLeaglePage::class, 'business_id', 'id');
    }

    public function blogs()
    {
        return $this->hasMany(Blog::class, 'business_id', 'id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'business_id', 'id');
    }


    public function paymentConfiguration()
    {
        return $this->hasOne(BusinessPaymentConfiguration::class, 'business_id');
    }

    public function customers()
    {
        return $this->belongsToMany(User::class, 'business_customers', 'business_id', 'customer_id');
    }

    public function customerRoles()
    {
        return $this->hasMany(BusinessCustomerRole::class, 'business_id', 'id');
    }

    public function offers()
    {
        return $this->hasMany(BusinessOffer::class, 'business_id', 'id');
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'favorite_item_id', 'id')->where('favorite_type', 'business');
    }

    // ***************************
    // for dashboard calculation end
    // ***************************

    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->city?->name,
            $this->state?->name,
            $this->country?->name,
        ]);

        $address = implode(', ', $parts);

        if (!empty($this->pincode)) {
            $address .= ($address ? ' - ' : '') . $this->pincode;
        }

        return $address;
    }

    /**
     * Scope query to only public active businesses.
     */
    public function scopePublic($query)
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereDoesntHave('businessSetting')
                    ->orWhereHas('businessSetting', function ($sub) {
                        $sub->where('is_private', false);
                    });
            });
    }

    /**
     * Scope a query to only include businesses within boundary coordinates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|string $swLat Southwest latitude
     * @param float|string $swLng Southwest longitude
     * @param float|string $neLat Northeast latitude
     * @param float|string $neLng Northeast longitude
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInBoundaries($query, $swLat, $swLng, $neLat, $neLng)
    {
        $minLat = min((float)$swLat, (float)$neLat);
        $maxLat = max((float)$swLat, (float)$neLat);
        $minLng = min((float)$swLng, (float)$neLng);
        $maxLng = max((float)$swLng, (float)$neLng);

        return $query->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereBetween('latitude', [$minLat, $maxLat])
            ->whereBetween('longitude', [$minLng, $maxLng]);
    }

    /**
     * Scope a query to find businesses within a given radius using the Haversine formula.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float|string $latitude
     * @param float|string $longitude
     * @param float|int $radius Radius in kilometers (default: 100)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNearby($query, $latitude, $longitude, $radius = 100)
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;
        $rad = !empty($radius) ? (float) $radius : 100.0;

        $haversine = "(6371 * acos(
            least(1.0, greatest(-1.0,
                cos(radians({$lat})) * cos(radians(businesses.latitude)) * cos(radians(businesses.longitude) - radians({$lng})) + sin(radians({$lat})) * sin(radians(businesses.latitude))
            ))
        ))";

        return $query->whereNotNull('businesses.latitude')
            ->whereNotNull('businesses.longitude')
            ->addSelect(\Illuminate\Support\Facades\DB::raw("{$haversine} AS distance"))
            ->whereRaw("{$haversine} <= ?", [$rad])
            ->orderByRaw("{$haversine} ASC");
    }
}
