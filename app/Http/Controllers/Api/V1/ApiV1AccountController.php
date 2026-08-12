<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\BusinessFavorite;
use App\Models\Order;

class ApiV1AccountController extends Controller
{
    public function profile(Request $request)
    {
        try {
            $user = $request->user();

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Profile retrieved successfully',
                'data' => $user->apiObject()
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'contact' => 'nullable|string|max:20',
                'dob' => 'nullable|date',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 422);
            }

            $user->first_name = trim($request->first_name);
            $user->last_name = trim($request->last_name);
            $user->contact = $request->contact;
            $user->dob = $request->dob;
            $user->save();

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->apiObject()
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 422);
            }

            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' => 'The provided current password does not match.',
                    'data' => null
                ], 422);
            }

            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Password updated successfully.',
                'data' => null
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function favorites(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = (int) $request->get('per_page', 10);

            $favorites = BusinessFavorite::with('business:id,name,business_image,rating,area,city_id', 'business.city:id,name')
                ->where('user_id', $user->id)
                ->latest()
                ->paginate($perPage);

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Favorites retrieved',
                'data' => $favorites->items(),
                'pagination' => [
                    'current_page' => $favorites->currentPage(),
                    'last_page' => $favorites->lastPage(),
                    'per_page' => $favorites->perPage(),
                    'total' => $favorites->total(),
                    'has_more' => $favorites->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function orders(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = (int) $request->get('per_page', 10);

            $orders = Order::with([
                'business:id,name,business_image',
                'items:id,order_id,item_id,item_name,price,quantity'
            ])
                ->where(function ($query) use ($user) {
                    $query->where('customer_id', $user->id)
                          ->orWhere('created_user_id', $user->id);
                })
                ->latest()
                ->paginate($perPage);

            $orders->through(function ($order) {
                if ($order->business) {
                    $order->business->business_image = getImage($order->business->business_image, 'business');
                }
                return $order;
            });

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders->items(),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                    'has_more' => $orders->hasMorePages(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function showOrder(Request $request, $id)
    {
        try {
            $user = $request->user();
            $order = Order::with([
                'business:id,name,business_image,area,city_id',
                'business.city:id,name',
                'items:id,order_id,item_id,item_name,price,quantity',
                'history'
            ])
                ->where(function ($query) use ($user) {
                    $query->where('customer_id', $user->id)
                          ->orWhere('created_user_id', $user->id);
                })
                ->where('id', $id)
                ->first();

            if (!$order) {
                return response()->json([
                    'status_code' => 404,
                    'success' => false,
                    'message' => 'Order not found',
                    'data' => null
                ], 404);
            }

            if ($order->business) {
                $order->business->business_image = getImage($order->business->business_image, 'business');
            }

            if ($order->business) {
                $order->business->business_image = getImage($order->business->business_image, 'business');
            }

            $userReview = \App\Models\ReviewAndRating::where('user_id', $user->id)
                ->where('business_id', $order->business_id)
                ->where('review_type', 'business')
                ->first();

            $orderArray = $order->toArray();
            $orderArray['user_review'] = $userReview;

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Order details retrieved successfully',
                'data' => $orderArray
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function submitOrderReview(Request $request)
    {
        try {
            $user = $request->user();

            $validator = Validator::make($request->all(), [
                'business_id' => 'required|exists:businesses,id',
                'order_id' => 'required|exists:orders,id',
                'rating' => 'required|numeric|min:1|max:5',
                'review' => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status_code' => 422,
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'data' => null
                ], 422);
            }

            $order = Order::where('id', $request->order_id)
                ->where(function ($query) use ($user) {
                    $query->where('customer_id', $user->id)
                          ->orWhere('created_user_id', $user->id);
                })
                ->first();

            if (!$order) {
                return response()->json([
                    'status_code' => 404,
                    'success' => false,
                    'message' => 'Order not found',
                    'data' => null
                ], 404);
            }

            if (strtolower($order->order_status) !== 'delivered') {
                return response()->json([
                    'status_code' => 400,
                    'success' => false,
                    'message' => 'Reviews can only be submitted for delivered orders',
                    'data' => null
                ], 400);
            }

            $review = \App\Models\ReviewAndRating::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'business_id' => $request->business_id,
                    'review_type' => 'business',
                ],
                [
                    'rating' => (float) $request->rating,
                    'review' => trim($request->review),
                ]
            );

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Review submitted successfully',
                'data' => $review
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
