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

    /**
     * Get the permissions for the user from session.
     *
     * @return array
     */
    public function getPosPermissionsAttribute()
    {
        return session('permissions', []);
    }

    /**
     * Get the business panel role name for the user from session.
     *
     * @return string
     */
    public function getRoleNameAttribute()
    {
        return session('role_name', $this->role == 'Business' ? 'Business Owner' : 'Staff Member');
    }

    /**
     * Get the current business name from session.
     *
     * @return string
     */
    public function getBusinessNameAttribute()
    {
        return session('business_name', 'My Business');
    }

    public function syncPermissionsToSession($businessId = null)
    {
        $permissions = [];
        $roleName = 'Staff Member';
        $businessName = 'My Business';

        if ($this->role == 'Business') {
            $permissions = ['all_access' => true];
            $roleName = 'Business Owner';

            // Get business name for owner
            $business = Business::where('owner_id', $this->id)->first();
            $businessName = $business->name ?? 'My Business';
        } else {
            // Get permissions from their role in the current business context
            if (!$businessId) {
                // Try guessing from the auth context
                $businessId = Auth::guard('pos')->check() ? getPosBusinessId() : getBusinessId();
            }

            $businessUser = BusinessUser::with(['role', 'business'])
                ->where('business_id', $businessId)
                ->where('user_id', $this->id)
                ->first();

            if ($businessUser) {
                if ($businessUser->role) {
                    $permissions = $businessUser->role->permissions ?? [];
                    $roleName = $businessUser->role->name;
                }
                $businessName = $businessUser->business->name ?? 'My Business';
            }
        }
        // Unified session keys used by both business and POS panels
        session(['permissions'   => $permissions]);
        session(['role_name'     => $roleName]);
        session(['business_name' => $businessName]);
    }
}
