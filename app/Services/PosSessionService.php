<?php

namespace App\Services;

use App\Models\User;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\Auth;

class PosSessionService
{
    /**
     * Synchronize all POS session data (active business details, user businesses list, permissions, role, settings).
     *
     * @param User|null $user
     * @param int|null $businessId
     * @return void
     */
    public function syncAll(?User $user, ?int $businessId = null): void
    {
        if (!$user) {
            return;
        }

        // Fetch accessible businesses for the user
        if ($user->role === 'Business') {
            $accessibleBusinesses = Business::where('owner_id', $user->id)
                ->whereIn('status', ['active', 'pending'])
                ->with(['city:id,name', 'state:id,name', 'businessCategory:id,name'])
                ->get();
        } else {
            $businessIds = BusinessUser::where('user_id', $user->id)->pluck('business_id');
            $accessibleBusinesses = Business::whereIn('id', $businessIds)
                ->whereIn('status', ['active', 'pending'])
                ->with(['city:id,name', 'state:id,name', 'businessCategory:id,name'])
                ->get();
        }

        if ($accessibleBusinesses->isEmpty()) {
            $this->clearPosSession();
            return;
        }

        // Determine active business
        $activeBusiness = null;
        if ($businessId) {
            $activeBusiness = $accessibleBusinesses->firstWhere('id', (int) $businessId);
        }

        if (empty($activeBusiness)) {
            $targetId = session('pos_business_id') ?: $user->business_id;
            $activeBusiness = $accessibleBusinesses->firstWhere('id', (int) $targetId) ?? $accessibleBusinesses->first();
        }

        $activeBusinessId = $activeBusiness->id;

        if ($user->business_id !== $activeBusinessId) {
            $user->business_id = $activeBusinessId;
            $user->save();
        }

        $getLogoUrl = function ($b) {
            $logoPath = $b->business_logo ?? $b->logo ?? $b->business_image ?? null;
            return !empty($logoPath) ? getImage($logoPath) : null;
        };

        // Create current business details object
        $businessDetails = (object) [
            'id' => $activeBusiness->id,
            'name' => $activeBusiness->name,
            'logo' => $getLogoUrl($activeBusiness),
            'contact' => $activeBusiness->contact,
            'email' => $activeBusiness->email,
            'address' => $activeBusiness->address,
            'full_address' => $activeBusiness->full_address,
            'category_name' => $activeBusiness->businessCategory?->name ?? '',
            'city_name' => $activeBusiness->city?->name ?? '',
            'state_name' => $activeBusiness->state?->name ?? '',
            'pincode' => $activeBusiness->pincode ?? '',
        ];

        // Create accessible businesses list for UI switcher
        $userBusinessesList = $accessibleBusinesses->map(function ($b) use ($getLogoUrl) {
            return (object) [
                'id' => $b->id,
                'name' => $b->name,
                'logo' => $getLogoUrl($b),
                'category_name' => $b->businessCategory?->name ?? '',
                'city_name' => $b->city?->name ?? '',
            ];
        })->values()->all();

        // Calculate permissions & role name
        $permissions = [];
        $roleName = 'Staff Member';

        if ($user->role === 'Business') {
            $permissions = ['all_access' => true];
            $roleName = 'Business Owner';
        } else {
            $businessUser = BusinessUser::with('role')
                ->where('business_id', $activeBusinessId)
                ->where('user_id', $user->id)
                ->first();

            if ($businessUser && $businessUser->role) {
                $permissions = $businessUser->role->permissions ?? [];
                $roleName = $businessUser->role->name ?? 'Staff Member';
            }
        }

        // Get business settings
        $businessSettings = function_exists('getBusinessSettings') ? getBusinessSettings($activeBusinessId, true) : null;

        session([
            'pos_business_id' => $activeBusinessId,
            'pos_business_details' => $businessDetails,
            'pos_user_businesses' => $userBusinessesList,
            'pos_permissions' => $permissions,
            'pos_role_name' => $roleName,
            'pos_business_name' => $activeBusiness->name,
            'pos_business_settings' => $businessSettings,
            'pos_permissions_last_synced' => now()->timestamp,
        ]);
    }

    /**
     * Check and sync permissions periodically (every 10 minutes) if user is not a Business Owner.
     *
     * @param User|null $user
     * @return void
     */
    public function checkPeriodicSync(?User $user): void
    {
        if (!$user || $user->role === 'Business') {
            return;
        }

        $lastSynced = session('pos_permissions_last_synced');

        // Re-sync permissions if missing or last synced more than 10 minutes (600 seconds) ago
        if (!$lastSynced || (now()->timestamp - (int) $lastSynced) >= 600) {
            $this->syncAll($user, session('pos_business_id'));
        }
    }

    /**
     * Get active POS business ID from session.
     *
     * @return int|null
     */
    public function getPosBusinessId(): ?int
    {
        if (session()->has('pos_business_id') && session('pos_business_id')) {
            return (int) session('pos_business_id');
        }
        $user = Auth::guard('pos')->user() ?? Auth::user();
        if ($user) {
            $this->syncAll($user);
            return session('pos_business_id') ? (int) session('pos_business_id') : null;
        }
        return null;
    }

    /**
     * Get active POS business details object from session.
     *
     * @return object|null
     */
    public function getPosBusinessDetails(): ?object
    {
        if (session()->has('pos_business_details')) {
            return session('pos_business_details');
        }
        $user = Auth::guard('pos')->user() ?? Auth::user();
        if ($user) {
            $this->syncAll($user);
            return session('pos_business_details');
        }
        return null;
    }

    /**
     * Get accessible user businesses list from session.
     *
     * @return array
     */
    public function getPosUserBusinesses(): array
    {
        if (session()->has('pos_user_businesses')) {
            return session('pos_user_businesses', []);
        }
        $user = Auth::guard('pos')->user() ?? Auth::user();
        if ($user) {
            $this->syncAll($user);
            return session('pos_user_businesses', []);
        }
        return [];
    }

    /**
     * Check if user has specific POS permission using session.
     *
     * @param string $permission
     * @return bool
     */
    public function checkPosPermission(string $permission): bool
    {
        $user = Auth::guard('pos')->user() ?? Auth::user();
        if (!$user) {
            return false;
        }

        if ($user->role === 'Business' || $user->role === 'Superadmin' || $user->role === 'Admin') {
            return true;
        }

        if (!session()->has('pos_permissions') || empty(session('pos_permissions'))) {
            $this->syncAll($user);
        } else {
            $this->checkPeriodicSync($user);
        }

        $permissions = session('pos_permissions', []);
        if (isset($permissions['all_access']) && $permissions['all_access']) {
            return true;
        }

        $posPermissions = $permissions['pos_permission'] ?? [];
        if (!empty($posPermissions[$permission])) {
            return true;
        }

        if (isset($permissions[$permission]) && $permissions[$permission]) {
            return true;
        }

        return false;
    }

    /**
     * Get allowed POS order statuses for the authenticated user.
     *
     * @return array
     */
    public function getPosAllowedOrderStatuses(): array
    {
        $user = Auth::guard('pos')->user() ?? Auth::user();
        $allStatuses = config('const.order_status', []);

        if (!$user) {
            return [];
        }

        if ($user->role === 'Business' || $user->role === 'Superadmin' || $user->role === 'Admin') {
            return $allStatuses;
        }

        if (!session()->has('pos_permissions') || empty(session('pos_permissions'))) {
            $this->syncAll($user);
        } else {
            $this->checkPeriodicSync($user);
        }

        $permissions = session('pos_permissions', []);
        if (isset($permissions['all_access']) && $permissions['all_access']) {
            return $allStatuses;
        }

        $posPermissions = $permissions['pos_permission'] ?? [];

        // Check inside pos_permission first, then fallback to permissions root for backward compatibility
        $allowed = null;
        if (is_array($posPermissions) && (array_key_exists('pos_order_statuses', $posPermissions) || array_key_exists('order_statuses', $posPermissions))) {
            $allowed = $posPermissions['pos_order_statuses'] ?? $posPermissions['order_statuses'] ?? [];
        } elseif (array_key_exists('pos_order_statuses', $permissions)) {
            $allowed = $permissions['pos_order_statuses'] ?? [];
        }

        // If not configured yet (legacy role), allow all
        if ($allowed === null) {
            return $allStatuses;
        }

        if (!is_array($allowed)) {
            $allowed = [];
        }

        if (in_array('all', $allowed, true)) {
            return $allStatuses;
        }

        $validMap = array_map('strtolower', $allStatuses);
        $result = [];
        foreach ($allowed as $status) {
            $low = strtolower($status);
            if (in_array($low, $validMap, true)) {
                $result[] = $low;
            }
        }

        return array_values(array_unique($result));
    }

    /**
     * Check if the authenticated user has access to a specific order status in POS.
     *
     * @param string $status
     * @return bool
     */
    public function checkPosOrderStatusAccess(string $status): bool
    {
        $allowed = $this->getPosAllowedOrderStatuses();
        return in_array(strtolower($status), array_map('strtolower', $allowed), true);
    }

    /**
     * Clear all POS session data.
     *
     * @return void
     */
    public function clearPosSession(): void
    {
        session()->forget([
            'pos_business_id',
            'pos_business_details',
            'pos_user_businesses',
            'pos_permissions',
            'pos_role_name',
            'pos_business_name',
            'pos_business_settings',
            'pos_permissions_last_synced',
        ]);
    }
}
