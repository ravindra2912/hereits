<?php

namespace App\Services;

use App\Models\User;
use App\Models\Business;
use App\Models\BusinessUser;

class BusinessSynchronizationService
{
    /**
     * Clear all active business data from session.
     *
     * @return void
     */
    public function clearBusinessSession(): void
    {
        session()->forget([
            'current_business_details',
            'business_settings',
            'business_settings_id',
            'business_permissions',
            'business_role_name',
            'business_name',
            'business_status',
            'business_front_url',
            'business_permissions_last_synced',
            'user_businesses_list',
            'user_details',
        ]);
    }

    /**
     * Synchronize all business session data (user, current business, business list, settings, permissions).
     *
     * @param User $user
     * @param int|null $businessId
     * @return void
     */
    public function syncAll(User $user, ?int $businessId = null): void
    {
        $activeBusinessId = $businessId;

        if (!$activeBusinessId) {
            if ($user->business_id) {
                $activeBusinessId = $user->business_id;
            } elseif ($user->role === 'Business') {
                $activeBusinessId = Business::where('owner_id', $user->id)->value('id');
            } else {
                $activeBusinessId = BusinessUser::with('role')
                    ->where('user_id', $user->id)
                    ->get()
                    ->filter(function ($bu) {
                        return !empty($bu->role?->permissions['business_access']);
                    })
                    ->pluck('business_id')
                    ->first();
            }
        }

        if ($activeBusinessId && $user->business_id !== $activeBusinessId) {
            $user->business_id = $activeBusinessId;
            $user->save();
        }

        $this->syncUserDetails($user);
        $this->syncUserBusinessesList($user);
        $this->syncCurrentBusinessDetails($activeBusinessId);
        $this->syncBusinessSettings($activeBusinessId);
        $this->syncPermissions($user, $activeBusinessId);
    }

    /**
     * Synchronize user details to session.
     *
     * @param User $user
     * @return void
     */
    public function syncUserDetails(User $user): void
    {
        session([
            'user_details' => (object) [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'contact' => $user->contact,
                'profile' => $user->profile,
                'dob' => $user->dob,
                'gender' => $user->gender,
                'business_id' => $user->business_id,
                'role' => $user->role,
            ]
        ]);
    }

    /**
     * Synchronize owned/accessible businesses list to session for switcher.
     *
     * @param User $user
     * @return void
     */
    public function syncUserBusinessesList(User $user): void
    {
        $ownedBusinessIds = Business::where('owner_id', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->pluck('id')
            ->toArray();

        $staffBusinessIds = BusinessUser::with('role')
            ->where('user_id', $user->id)
            ->get()
            ->filter(function ($bu) {
                return !empty($bu->role?->permissions['business_access']);
            })
            ->pluck('business_id')
            ->toArray();

        $allAccessibleIds = array_values(array_unique(array_merge($ownedBusinessIds, $staffBusinessIds)));

        $businesses = Business::whereIn('id', $allAccessibleIds)
            ->select(['id', 'owner_id', 'name', 'slug', 'business_logo', 'business_image', 'address', 'area', 'pincode', 'contact', 'status', 'city_id', 'state_id', 'country_id'])
            ->with([
                'city:id,name',
                'state:id,name',
                'country:id,name',
            ])
            ->whereIn('status', ['active', 'pending'])
            ->get();

        session(['user_businesses_list' => $businesses]);
    }

    /**
     * Synchronize active business details to session.
     *
     * @param int|null $businessId
     * @return void
     */
    public function syncCurrentBusinessDetails(?int $businessId = null): void
    {
        $currentBusiness = null;
        if ($businessId) {
            $currentBusiness = Business::with([
                'city:id,name',
                'state:id,name',
                'country:id,name',
            ])->find($businessId);
        }

        if ($currentBusiness) {
            session([
                'current_business_details' => (object) [
                    'id' => $currentBusiness->id,
                    'owner_id' => $currentBusiness->owner_id,
                    'name' => $currentBusiness->name,
                    'slug' => $currentBusiness->slug,
                    'business_logo' => $currentBusiness->business_logo,
                    'business_image' => $currentBusiness->business_image,
                    'address' => $currentBusiness->address,
                    'contact' => $currentBusiness->contact,
                    'full_address' => $currentBusiness->full_address,
                    'status' => $currentBusiness->status,
                    'city_id' => $currentBusiness->city_id,
                    'state_id' => $currentBusiness->state_id,
                    'country_id' => $currentBusiness->country_id,
                ]
            ]);
        } else {
            session(['current_business_details' => null]);
        }
    }

    /**
     * Synchronize active business settings to session.
     *
     * @param int|null $businessId
     * @param bool $forceRefresh
     * @return void
     */
    public function syncBusinessSettings(?int $businessId = null, bool $forceRefresh = true): void
    {
        if ($businessId && function_exists('getBusinessSettings')) {
            getBusinessSettings($businessId, $forceRefresh);
        }
    }

    /**
     * Synchronize business permissions, role name, status, and URL to session.
     *
     * @param User $user
     * @param int|null $businessId
     * @return void
     */
    public function syncPermissions(User $user, ?int $businessId = null): void
    {
        $permissions = [];
        $roleName = 'Staff Member';
        $businessName = 'My Business';
        $businessFrontUrl = '#';
        $businessStatus = 'active';

        $targetBusinessId = $businessId ?: $user->business_id;

        // Check if user is owner of the target business
        $isOwner = false;
        if ($targetBusinessId) {
            $isOwner = Business::where('id', $targetBusinessId)->where('owner_id', $user->id)->exists();
        } elseif ($user->role === 'Business') {
            $isOwner = true;
            $targetBusinessId = Business::where('owner_id', $user->id)->value('id');
        }

        if ($isOwner) {
            $permissions = ['all_access' => true, 'business_access' => true];
            $roleName = 'Business Owner';

            $business = Business::find($targetBusinessId);
            $businessName = $business->name ?? 'My Business';
            $businessStatus = $business->status ?? 'active';
            $businessFrontUrl = $business && $business->slug ? route('business-details', $business->slug) : '#';
        } else {
            if (!$targetBusinessId) {
                $businessUser = BusinessUser::with('role')
                    ->where('user_id', $user->id)
                    ->get()
                    ->filter(function ($bu) {
                        return !empty($bu->role?->permissions['business_access']);
                    })
                    ->first();

                if ($businessUser) {
                    $targetBusinessId = $businessUser->business_id;
                    $user->business_id = $targetBusinessId;
                    $user->save();
                }
            }

            if ($targetBusinessId) {
                $businessUser = BusinessUser::with(['role', 'business'])
                    ->where('business_id', $targetBusinessId)
                    ->where('user_id', $user->id)
                    ->first();

                if ($businessUser) {
                    if ($businessUser->role) {
                        $permissions = $businessUser->role->permissions ?? [];
                        $roleName = $businessUser->role->name ?? 'Staff Member';
                    }
                    $businessName = $businessUser->business->name ?? 'My Business';
                    $businessStatus = $businessUser->business->status ?? 'active';
                    $businessFrontUrl = $businessUser->business && $businessUser->business->slug ? route('business-details', $businessUser->business->slug) : '#';
                }
            }
        }

        session(['business_permissions' => $permissions]);
        session(['business_role_name' => $roleName]);
        session(['business_name' => $businessName]);
        session(['business_status' => $businessStatus ?? 'active']);
        session(['business_front_url' => $businessFrontUrl]);
        session(['business_permissions_last_synced' => now()]);
    }
}
