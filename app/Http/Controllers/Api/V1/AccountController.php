<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Order;
use App\Models\UserCreditTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    public function profile(Request $request)
    {
        try {
            $user = $request->user();
            $data = $user->apiObject();
            
            // Calculate credit balance
            $totalCredit = UserCreditTransaction::where('user_id', $user->id)
                ->where('type', 'credit')
                ->sum('amount');
            $totalDebit = UserCreditTransaction::where('user_id', $user->id)
                ->where('type', 'debit')
                ->sum('amount');
                
            $data['credit_balance'] = $totalCredit - $totalDebit;

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Profile details',
                'data' => $data
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
                'first_name' => 'required|string|max:191',
                'last_name' => 'required|string|max:191',
                'contact' => 'nullable|numeric|digits:10|unique:users,contact,' . $user->id,
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

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            if ($request->has('contact')) {
                $user->contact = $request->contact;
            }
            if ($request->has('dob')) {
                $user->dob = $request->dob;
            }
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

    public function favorites(Request $request)
    {
        try {
            $user = $request->user();
            $favorites = Favorite::with('business:id,name,address')
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Favorites retrieved',
                'data' => $favorites
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function orders(Request $request)
    {
        try {
            $user = $request->user();
            $orders = Order::with('orderItems')
                ->where('user_id', $user->id)
                ->latest()
                ->get();

            return response()->json([
                'status_code' => 200,
                'success' => true,
                'message' => 'Orders retrieved',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json(['status_code' => 500, 'success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
