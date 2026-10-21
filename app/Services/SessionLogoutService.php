<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\DB;

class SessionLogoutService
{
    /**
     * Terminate all other sessions for a specific user except the current session.
     * Enforces single device/browser login per user.
     *
     * @param int|string $userId
     * @param string|null $currentSessionId
     * @return int Number of deleted sessions
     */
    public function logoutOtherDevices(int|string $userId, ?string $currentSessionId = null): int
    {
        $currentSessionId = $currentSessionId ?? session()->getId();
        $sessionIds = $this->findSessionIdsForUsers([$userId]);

        // Exclude current session
        $sessionIds = array_values(array_diff($sessionIds, [$currentSessionId]));

        if (empty($sessionIds)) {
            return 0;
        }

        return DB::table('sessions')
            ->whereIn('id', $sessionIds)
            ->delete();
    }

    /**
     * Terminate all active sessions for one or multiple users completely.
     *
     * @param int|string|array $userIds
     * @return int Number of deleted sessions
     */
    public function logoutUser(int|string|array $userIds): int
    {
        $userIds = is_array($userIds) ? $userIds : [$userIds];
        $userIds = array_values(array_filter(array_unique($userIds)));

        if (empty($userIds)) {
            return 0;
        }

        $sessionIds = $this->findSessionIdsForUsers($userIds);

        if (empty($sessionIds)) {
            return 0;
        }

        return DB::table('sessions')
            ->whereIn('id', $sessionIds)
            ->delete();
    }

    /**
     * Terminate all sessions for staff users assigned to one or more roles.
     * Used when a role's permissions/name are updated or when the role is deleted.
     *
     * @param int|string|array $roleIds
     * @param int|string|null $businessId
     * @return int Number of deleted sessions
     */
    public function logoutRoleUsers(int|string|array $roleIds, int|string|null $businessId = null): int
    {
        $roleIds = is_array($roleIds) ? $roleIds : [$roleIds];
        $roleIds = array_values(array_filter(array_unique($roleIds)));

        if (empty($roleIds)) {
            return 0;
        }

        $query = BusinessUser::whereIn('role_id', $roleIds);
        if ($businessId) {
            $query->where('business_id', $businessId);
        }

        $userIds = $query->pluck('user_id')->unique()->toArray();

        return $this->logoutUser($userIds);
    }

    /**
     * Terminate all sessions for all staff members (and optionally owner) of a business.
     * Used when business access is toggled, expired, or deactivated.
     *
     * @param int|string $businessId
     * @param bool $includeOwner
     * @return int Number of deleted sessions
     */
    public function logoutBusinessUsers(int|string $businessId, bool $includeOwner = false): int
    {
        $userIds = BusinessUser::where('business_id', $businessId)
            ->pluck('user_id')
            ->toArray();

        if ($includeOwner) {
            $business = Business::find($businessId);
            if ($business && $business->owner_id) {
                $userIds[] = $business->owner_id;
            }
        }

        return $this->logoutUser(array_unique($userIds));
    }

    /**
     * Find all session IDs associated with given user IDs across all guards (web, pos, admin, expert)
     * by checking both the user_id column and decoding the session payload.
     *
     * @param array $userIds
     * @return array
     */
    protected function findSessionIdsForUsers(array $userIds): array
    {
        $userIds = array_map('intval', array_values(array_filter(array_unique($userIds))));
        if (empty($userIds)) {
            return [];
        }

        $sessions = DB::table('sessions')->select('id', 'user_id', 'payload')->get();
        $matchedSessionIds = [];

        foreach ($sessions as $session) {
            // Check direct user_id column
            if (!empty($session->user_id) && in_array((int)$session->user_id, $userIds, true)) {
                $matchedSessionIds[] = $session->id;
                continue;
            }

            // Inspect payload for any guard login key (login_web_*, login_pos_*, login_admin_*, login_expert_*)
            if (!empty($session->payload)) {
                $payloadData = @unserialize(base64_decode($session->payload));
                if (is_array($payloadData)) {
                    foreach ($payloadData as $key => $value) {
                        if (str_starts_with($key, 'login_') && in_array((int)$value, $userIds, true)) {
                            $matchedSessionIds[] = $session->id;
                            break;
                        }
                    }
                }
            }
        }

        return array_unique($matchedSessionIds);
    }
}

