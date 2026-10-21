<?php

namespace App\Http\Controllers\Front;

use App\Models\User;
use App\Models\Business;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Models\BusinessCategory;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AppointmentBooking;
use App\Models\ReviewAndRating;
use App\Models\Favorite;
use App\Models\Product;
use App\Models\Service;
use App\Models\Expert;
use App\Models\Order;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('front.account.index');
    }
    public function userProfile(Request $request): View
    {
        $user = User::find(Auth::user()->id);
        if ($user) {
            return view('front.account.profile.user_profile', compact('user'));
        }
        return view('errors.404');
    }

    public function userProfileUpdate(Request $request, $id)
    {
        if ((int)$id !== (int)Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('account.userprofile');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'profile' => 'nullable|mimes:jpg,jpeg,png,webp|',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|email|unique:users,email,' . $id,
                'contact' => 'required|numeric|unique:users,contact,' . $id,
                'dob' => 'nullable|date',
                'gender' => 'nullable',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {

                $update = Auth::user();

                if ($request->hasFile('profile')) {
                    $oldimage = $update->profile;
                    $image_name = fileUploadStorage($request->file('profile'), 'user_images', 500, 500);
                    $update->profile = $image_name;
                }

                $update->first_name = $request->first_name;
                $update->last_name = $request->last_name;
                $update->email = $request->email;
                $update->contact = $request->contact;
                $update->dob = $request->dob;
                $update->gender = $request->gender;
                $update->save();

                // Remove old uploaded image if exist
                if (isset($oldimage)) {
                    fileRemoveStorage($oldimage);
                }

                $success = true;
                $message = 'Profile updated successfully.';
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
            if (isset($image_name) && !empty($image_name)) {
                fileRemoveStorage($image_name);
            }
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function changePassword(Request $request): View
    {

        return view('front.account.profile.changepassword');
    }

    public function changePasswordUpdate(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = Route('account.changePassword');
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'old_password' => 'required',
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                $message = $validator->errors();
                // $message = $validator->errors()->first();
            } else {
                $user = User::lockForUpdate()->find(Auth::user()->id);
                if (password_verify($request->old_password, $user->password)) {
                    $user->password = bcrypt($request->password);
                    $user->save();
                    $success = true;
                    $message = 'Password updated successfully.';
                    DB::commit();
                } else {
                    $message = 'Old password does not match.';
                    DB::rollBack();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function booking(): View
    {
        return view('front.account.booking.bookings');
    }

    public function getBookings(Request $request)
    {
        $bookings = AppointmentBooking::query()
            ->with(['business:id,name', 'expert:id,user_id', 'expert.user:id,first_name,last_name'])
            ->orderBy('id', 'desc')
            ->where('user_id', Auth::user()->id)
            ->limit($request->limit)
            ->skip($request->offset)
            ->get();

        $data['list'] =  view('front.account.booking.elements.bookingList', compact('bookings'))->render();
        $data['counts'] =  $bookings->count();
        return response()->json($data);
    }

    public function bookingDetails(Request $request, $id): View
    {
        $booking = AppointmentBooking::with([
            'business:id,name',
            'expert:id,user_id',
            'expert.user:id,first_name,last_name',
            'review:id,rating,review',
            'appointmentProducts'
        ])
            ->where('id', $id)
            ->where('user_id', Auth::user()->id)
            ->first();
        if ($booking) {
            return view('front.account.booking.bookingDetails', compact('booking'));
        }
        return view('errors.404');
    }

    public function bookingCancel(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        try {
            DB::beginTransaction();
            $rules = [
                'booking_id' => 'required|exists:appointment_bookings,id',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                // $message = $validator->errors();
                $message = $validator->errors()->first();
            } else {

                $booking = AppointmentBooking::select('id', 'business_id', 'expert_id', 'booking_date', 'token_number', 'status')
                    ->where('user_id', Auth::user()->id)
                    ->where('id', $request->booking_id)
                    ->lockForUpdate()
                    ->first();
                if ($booking && ($booking->status == 'pending' || $booking->status == 'confirmed')) {

                    // Get all booking with token number greater than current booking
                    // $getAllbooking = AppointmentBooking::select('id', 'token_number')
                    //     ->where('expert_id', $booking->expert_id)
                    //     ->where('booking_date', $booking->booking_date)
                    //     ->where('status', 'pending')
                    //     ->where('token_number', '>', $booking->token_number)
                    //     ->where('business_id', $booking->business_id)
                    //     ->orderBy('token_number', 'asc')
                    //     ->get();
                    // if ($getAllbooking) {
                    //     // Decrease token number of all booking
                    //     foreach ($getAllbooking as $value) {
                    //         $value->token_number = $value->token_number - 1;
                    //         $value->save();
                    //     }
                    // }

                    $booking->status = 'cancel_by_user';
                    $booking->save();
                    $success = true;
                    $message = 'Booking Cancel successfully.';
                    DB::commit();
                } else {
                    $message = 'Booking not found.';
                    DB::rollBack();
                }
            }
        } catch (\Exception $e) {
            DB::rollBack();
            // $message = $e;
            $message = checkResponse($e);
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    function bookingReview(Request $request)
    {
        $success = false;
        $message = 'Something Wrong!';
        $redirect = '';
        $data = array();

        DB::beginTransaction();
        try {
            $rules = [
                'booking_id' => 'required|exists:appointment_bookings,id',
                'rating' => 'required|numeric|min:1|max:5',
                'review' => 'nullable|string|max:500',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) { // Validation fails
                // $message = $validator->errors();
                $message = $validator->errors()->first();
            } else {
                $booking = AppointmentBooking::select('id', 'business_id', 'expert_id', 'status', 'review_id')
                    ->where('user_id', Auth::user()->id)
                    ->where('id', $request->booking_id)
                    ->first();
                if ($booking) {
                    if ($booking->status == 'completed') {
                        if ($booking->review_id == null) {

                            $insert = new ReviewAndRating();
                            $insert->business_id = $booking->business_id;
                            $insert->review_on_id = $booking->expert_id;
                            $insert->user_id = Auth::user()->id;
                            $insert->rating = $request->rating;
                            $insert->review = $request->review;
                            $insert->review_type = 'expert';
                            $insert->save();

                            $booking->review_id = $insert->id;
                            $booking->save();

                            $success = true;
                            $message = 'Review added successfully.';
                            DB::commit();
                        } else {
                            $message = 'You have already added review.';
                        }
                    } else {
                        $message = 'Booking not completed yet.';
                    }
                } else {
                    $message = 'Booking not found.';
                }
            }
        } catch (\Exception $e) {
            $message = checkResponse($e);
            DB::rollBack();
        }
        return response()->json(['success' => $success, 'message' => $message, 'data' => $data, 'redirect' => $redirect]);
    }

    public function orders(): View
    {
        $orders = \App\Models\Order::where('customer_id', Auth::user()->id)
            ->with(['business:id,name'])
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('front.account.order.orders', compact('orders'));
    }

    public function orderDetails($id): View
    {
        $order = Order::where('id', $id)
            ->where('customer_id', Auth::user()->id)
            ->with(['business:id,name', 'items', 'transactions'])
            ->firstOrFail();
        return view('front.account.order.order_details', compact('order'));
    }

    public function quotations(Request $request): View
    {
        $quotations = Quotation::whereHas('customer', function ($q) {
                $q->where('customer_id', Auth::user()->id);
            })
            ->with(['business:id,name,slug,business_image,contact', 'items'])
            ->orderBy('id', 'desc')
            ->paginate(10);
        return view('front.account.quotation.quotations', compact('quotations'));
    }

    public function quotationDetails($id): View
    {
        $quotation = Quotation::where('id', $id)
            ->whereHas('customer', function ($q) {
                $q->where('customer_id', Auth::user()->id);
            })
            ->with([
                'business:id,name,slug,business_image,business_logo,contact,address',
                'items.product:id,name,sku,sell_price',
                'order:id,invoice_number'
            ])
            ->firstOrFail();
        return view('front.account.quotation.quotation_details', compact('quotation'));
    }

    /**
     * Display the user's favorite products, services, and experts.
     */
    public function favorites(Request $request): View
    {
        $userId = Auth::id();
        $type = $request->query('type', 'all');

        $query = Favorite::where('user_id', $userId)
            ->with([
                'business:id,name,slug,business_image,business_logo',
                'product' => function ($q) {
                    $q->select('id', 'name', 'slug', 'description', 'price', 'sell_price', 'price_type', 'min_price', 'max_price', 'business_id', 'category_id', 'status')
                        ->with([
                            'firstTwoImage:id,product_id,image_url',
                            'category:id,name,slug'
                        ]);
                },
                'service' => function ($q) {
                    $q->select('id', 'name', 'slug', 'description', 'price', 'price_type', 'min_price', 'max_price', 'image_url', 'business_id', 'category_id', 'status')
                        ->with([
                            'category:id,name,slug'
                        ]);
                },
                'expert' => function ($q) {
                    $q->select('id', 'user_id', 'slug', 'title', 'rating', 'business_id', 'department_id', 'status')
                        ->with([
                            'department:id,department_name',
                            'user:id,first_name,last_name,profile',
                        ]);
                }
            ]);

        if (in_array($type, ['product', 'service', 'expert'])) {
            $query->where('favorite_type', $type);
        }

        $favorites = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        $counts = [
            'all' => Favorite::where('user_id', $userId)->count(),
            'product' => Favorite::where('user_id', $userId)->where('favorite_type', 'product')->count(),
            'service' => Favorite::where('user_id', $userId)->where('favorite_type', 'service')->count(),
            'expert' => Favorite::where('user_id', $userId)->where('favorite_type', 'expert')->count(),
        ];

        return view('front.account.favorites.index', compact('favorites', 'counts', 'type'));
    }

    /**
     * Toggle favorite status for product, service, expert, or business.
     */
    public function toggleFavorite(Request $request, ?string $business_slug = null)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'requires_login' => true,
                'message' => 'Please login to save your favorites.',
                'redirect' => route('login'),
            ], 401);
        }

        $request->validate([
            'favorite_type' => 'required|string|in:product,service,expert,business',
            'favorite_item_id' => 'required|integer',
            'business_id' => 'nullable|integer',
        ]);

        $userId = Auth::id();
        $type = $request->favorite_type;
        $itemId = (int)$request->favorite_item_id;
        $businessId = $request->business_id;

        try {
            if (empty($businessId)) {
                if ($type === 'product') {
                    $item = Product::select('id', 'business_id')->find($itemId);
                    $businessId = $item?->business_id;
                } elseif ($type === 'service') {
                    $item = Service::select('id', 'business_id')->find($itemId);
                    $businessId = $item?->business_id;
                } elseif ($type === 'expert') {
                    $item = Expert::select('id', 'business_id')->find($itemId);
                    $businessId = $item?->business_id;
                } elseif ($type === 'business') {
                    $businessId = $itemId;
                }
            }

            if (empty($businessId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item or business not found.',
                ], 404);
            }

            $existing = Favorite::where('user_id', $userId)
                ->where('favorite_type', $type)
                ->where('favorite_item_id', $itemId)
                ->first();

            if ($existing) {
                $existing->delete();
                return response()->json([
                    'success' => true,
                    'is_favorite' => false,
                    'action' => 'removed',
                    'message' => ucfirst($type) . ' removed from favorites.',
                ]);
            }

            Favorite::create([
                'user_id' => $userId,
                'business_id' => $businessId,
                'favorite_type' => $type,
                'favorite_item_id' => $itemId,
            ]);

            return response()->json([
                'success' => true,
                'is_favorite' => true,
                'action' => 'added',
                'message' => ucfirst($type) . ' added to favorites.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e),
            ], 500);
        }
    }

    /**
     * Remove favorite by favorite ID.
     */
    public function removeFavorite(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'requires_login' => true,
                'message' => 'Please login to manage favorites.',
            ], 401);
        }

        $request->validate([
            'id' => 'required|integer',
        ]);

        try {
            $favorite = Favorite::where('user_id', Auth::id())
                ->where('id', $request->id)
                ->first();

            if ($favorite) {
                $favorite->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'Removed from favorites.',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Favorite not found.',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => checkResponse($e),
            ], 500);
        }
    }
}
