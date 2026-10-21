<?php

namespace App\Services\Api\V1;

use App\Models\Order;
use App\Models\ReviewAndRating;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ApiV1OrderService
{
    /**
     * Get paginated orders for a user.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUserOrders(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Order::select([
            'id',
            'business_id',
            'invoice_number',
            'customer_id',
            'customer_name',
            'customer_contact',
            'total',
            'subtotal',
            'discount',
            'total_tax',
            'shipping_charge',
            'packing_charge',
            'order_status',
            'payment_status',
            'created_at',
        ])
            ->where('customer_id', $userId)
            ->with([
                'business:id,name,contact,address,business_image,business_logo',
                'items:id,order_id,item_id,item_name,price,quantity',
            ])
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get specific order details for a user.
     *
     * @param int $userId
     * @param int $orderId
     * @return Order|null
     */
    public function getUserOrderDetail(int $userId, int $orderId): ?Order
    {
        return Order::where('id', $orderId)
            ->where('customer_id', $userId)
            ->with([
                'business:id,name,contact,address,business_image,business_logo',
                'items:id,order_id,item_id,item_name,price,quantity',
            ])
            ->first();
    }

    /**
     * Submit user review for a completed order.
     *
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function submitOrderReview(int $userId, array $data): array
    {
        $orderId = (int)($data['order_id'] ?? 0);
        $businessId = (int)($data['business_id'] ?? 0);
        $rating = (float)($data['rating'] ?? 5);
        $reviewText = trim($data['review'] ?? '');

        $order = Order::where('id', $orderId)
            ->where('customer_id', $userId)
            ->first();

        if (!$order) {
            return [
                'success' => false,
                'message' => 'Order not found or unauthorized.',
                'code' => 404,
            ];
        }

        DB::beginTransaction();
        try {
            $existingReview = ReviewAndRating::where('user_id', $userId)
                ->where('business_id', $businessId ?: $order->business_id)
                ->where('review_on_id', $orderId)
                ->first();

            if ($existingReview) {
                $existingReview->rating = $rating;
                $existingReview->review = $reviewText;
                $existingReview->save();
                $reviewModel = $existingReview;
            } else {
                $reviewModel = ReviewAndRating::create([
                    'user_id' => $userId,
                    'business_id' => $businessId ?: $order->business_id,
                    'review_on_id' => $orderId,
                    'review_type' => 'business',
                    'rating' => $rating,
                    'review' => $reviewText,
                ]);
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Review submitted successfully.',
                'data' => [
                    'id' => $reviewModel->id,
                    'rating' => (int)$reviewModel->rating,
                    'review' => $reviewModel->review,
                    'created_at' => $reviewModel->created_at?->toIso8601String(),
                ],
                'code' => 200,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Failed to submit review: ' . $e->getMessage(),
                'code' => 500,
            ];
        }
    }
}
