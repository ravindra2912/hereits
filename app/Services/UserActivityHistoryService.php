<?php

namespace App\Services;

use App\Models\UserActivityHistory;

class UserActivityHistoryService
{
    /**
     * Log a user activity history.
     *
     * @param int $businessId
     * @param int $userId
     * @param string $type
     * @param int $sourceId
     * @param string $title
     * @param string|null $remark
     * @return UserActivityHistory
     */
    public function logActivity(int $businessId, int $userId, string $type, int $sourceId, string $title, ?string $remark = null): UserActivityHistory
    {
        return UserActivityHistory::create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'type' => $type,
            'source_id' => $sourceId,
            'title' => $title,
            'remark' => $remark,
        ]);
    }

    /**
     * Log an order placement activity.
     *
     * @param int $businessId
     * @param int $userId
     * @param int $orderId
     * @param string $remark
     * @return UserActivityHistory
     */
    public function logOrderPlacement(int $businessId, int $userId, int $orderId, string $remark = 'Order placed successfully from POS.'): UserActivityHistory
    {
        return $this->logActivity($businessId, $userId, 'order', $orderId, 'Order Placed', $remark);
    }
}
