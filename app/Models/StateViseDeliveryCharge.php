<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StateViseDeliveryCharge extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'state_vise_delivery_charges';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'business_id',
        'state_id',
        'delivery_charge_per_kg',
    ];

    /**
     * Get the business that owns this charge.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * Get the state IDs as an array.
     *
     * @return array
     */
    public function getStateIdsAttribute()
    {
        return $this->state_id ? explode(',', $this->state_id) : [];
    }

    /**
     * Get the associated State models.
     */
    public function getStates()
    {
        $ids = $this->state_ids;
        if (empty($ids)) {
            return collect();
        }
        return State::select('id', 'name')->whereIn('id', $ids)->get();
    }
}
