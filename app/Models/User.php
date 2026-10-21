<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'contact',
        'dob',
        'gender',
        'profile',
        'business_id',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Sanitize contact before saving to BIGINT column.
     */
    protected function contact(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            set: fn ($value) => !empty($value) ? (preg_replace('/[^0-9]/', '', (string)$value) ?: null) : null,
        );
    }

    /**
     * Get full name of the user.
     */
    public function getNameAttribute(): string
    {
        return trim(($this->first_name ?? '') . ' ' . ($this->last_name ?? ''));
    }

    public function getBusinessDetails()
    {
        return $this->belongsTo(Business::class, 'business_id', 'id');
    }

    public function getBusinesses()
    {
        return $this->hasMany(Business::class, 'owner_id', 'id');
    }

    public function appointments()
    {
        return $this->hasMany(AppointmentBooking::class, 'user_id');
    }

    public function devices()
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function experts()
    {
        return $this->hasMany(Expert::class, 'user_id');
    }

    //+++++++++++++++ For api responce ================
    public function apiObject(): array
    {
        $data = [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'profile' => getImage($this->profile),
            'email' => $this->email,
            'contact' => (int)$this->contact,
            'dob' => $this->dob,
            'gender' => $this->gender
        ];

        return $data;
    }
    public function businessUsers()
    {
        return $this->hasMany(BusinessUser::class, 'user_id');
    }

    public function businessCustomers()
    {
        return $this->hasMany(BusinessCustomer::class, 'customer_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function quotations()
    {
        return $this->hasManyThrough(Quotation::class, BusinessCustomer::class, 'customer_id', 'business_customer_id', 'id', 'id');
    }

    /**
     * Get the POS permissions for the user from session.
     * 
     * @return array
     */
    public function getPosPermissionsAttribute()
    {
        return session('pos_permissions', []);
    }

    /**
     * Get the POS role name for the user from session.
     * 
     * @return string
     */
    public function getRoleNameAttribute()
    {
        return session('pos_role_name', $this->role == 'Business' ? 'Business Owner' : 'Staff Member');
    }

    /**
     * Get the current POS business name from session.
     * 
     * @return string
     */
    public function getBusinessNameAttribute()
    {
        return session('pos_business_name', 'My Business');
    }

    public function syncPermissionsToSession($businessId = null)
    {
        if (function_exists('syncPosSessionData')) {
            syncPosSessionData($this, $businessId);
        }
    }

    public function syncBusinessPermissionsToSession($businessId = null)
    {
        app(\App\Services\BusinessSynchronizationService::class)->syncPermissions($this, $businessId);
    }

    /**
     * Synchronize all required user, business, list, and setting data to session.
     */
    public function syncBusinessSessionData($businessId = null)
    {
        app(\App\Services\BusinessSynchronizationService::class)->syncAll($this, $businessId);
    }
}
