<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'image',
        'deduct_credit_per_customer_appointment',
        'deduct_credit_per_self_appointment',
        'status',
    ];

    /**
     * Get the businesses for the category.
     */
    public function businesses()
    {
        return $this->hasMany(Business::class, 'business_category_id', 'id');
    }

    /**
     * Scope categories that have active public businesses within boundaries.
     */
    public function scopeInBoundaries($query, $swLat, $swLng, $neLat, $neLng)
    {
        return $query->whereHas('businesses', function ($q) use ($swLat, $swLng, $neLat, $neLng) {
            $q->public()->inBoundaries($swLat, $swLng, $neLat, $neLng);
        });
    }

    /**
     * Scope categories that have active public businesses nearby.
     */
    public function scopeNearby($query, $latitude, $longitude, $radius = 100)
    {
        return $query->whereHas('businesses', function ($q) use ($latitude, $longitude, $radius) {
            $q->public()->nearby($latitude, $longitude, $radius);
        });
    }
}
