<?php

use Carbon\Carbon;
use App\Models\City;
use App\Models\User;
use App\Models\State;
use GuzzleHttp\Client;
// use Google\Client;
use App\Models\Country;
use Illuminate\Support\Str;
use App\Models\Expert;
use chillerlan\QRCode\QRCode;
use App\Models\BusinessTiming;
use App\Models\BusinessSetting;
use App\Models\BusinessCategory;
use chillerlan\QRCode\QROptions;
use App\Models\AppointmentBooking;
use App\Models\Business;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Purchase;
use App\Traits\GoogleDrive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use chillerlan\QRCode\Common\EccLevel;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use chillerlan\QRCode\Output\QROutputInterface;


function apiResponce($statuscode, $status, $message, $data = [])
{
    return response()->json(["code" => $statuscode, "success" => $status, "message" => $message, "data" => $data]);
}

/**
 * Sanitize and format exception/error messages for safe user response.
 * In production or when debug mode is disabled, logs the full exception
 * and returns a safe generic message to prevent exposing database/system internals.
 *
 * @param \Throwable|string|mixed $exception
 * @param string $customMessage
 * @return string
 */
function checkResponce($exception, string $customMessage = 'Something went wrong, please try again later.'): string
{
    if ($exception instanceof \Throwable) {
        Log::error('Exception caught in checkResponce: ' . $exception->getMessage(), [
            'exception' => $exception,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        if (app()->environment('production') || !config('app.debug')) {
            return $customMessage;
        }

        return $exception->getMessage();
    }

    if (is_string($exception)) {
        if (app()->environment('production') || !config('app.debug')) {
            $sensitivePatterns = ['SQLSTATE', 'QueryException', 'PDOException', 'syntax error', 'Column not found', 'Table '];
            foreach ($sensitivePatterns as $pattern) {
                if (stripos($exception, $pattern) !== false) {
                    Log::error('Sensitive error message sanitized in checkResponce: ' . $exception);
                    return $customMessage;
                }
            }
        }

        return $exception;
    }

    return $customMessage;
}

if (!function_exists('checkResponse')) {
    /**
     * Alias for checkResponce.
     */
    function checkResponse($exception, string $customMessage = 'Something went wrong, please try again later.'): string
    {
        return checkResponce($exception, $customMessage);
    }
}

/**
 * Check if an item (product, service, expert, business) is in user's favorites.
 * Uses request-level static memory cache to prevent N+1 queries during blade rendering.
 *
 * @param string $type
 * @param int|string $itemId
 * @param int|null $userId
 * @return bool
 */
function isFavorite(string $type, $itemId, ?int $userId = null): bool
{
    $uid = $userId ?? (auth('api')->id() ?? (Auth::check() ? Auth::id() : null));
    if (!$uid || empty($itemId)) {
        return false;
    }

    static $userFavoritesCache = [];

    if (!isset($userFavoritesCache[$uid])) {
        $userFavoritesCache[$uid] = \App\Models\Favorite::where('user_id', $uid)
            ->select('favorite_type', 'favorite_item_id')
            ->get()
            ->groupBy('favorite_type')
            ->map(function ($items) {
                return $items->pluck('favorite_item_id')->map(fn($id) => (int)$id)->flip()->all();
            })
            ->all();
    }

    return isset($userFavoritesCache[$uid][$type][(int)$itemId]);
}


// ************ image function start ***************

function imageService(): \App\Services\ImageService
{
    return app(\App\Services\ImageService::class);
}

function storeImage($imageObject, string $directory = "", $width = "", $height = "", string $convertTo = "webp"): string
{
    return imageService()->storeImage($imageObject, $directory, $width, $height, $convertTo);
}

function removeImage(?string $imagePath): bool
{
    return imageService()->removeImage($imagePath);
}

function fileRemoveStorage($imageObject)
{
    return imageService()->removeImage($imageObject);
}

function fileUploadStorage($imageObject, $directory = "", $width = "", $hieght = "", $converto = "webp")
{
    return imageService()->storeImage($imageObject, $directory, $width, $hieght, $converto);
}

function getImage($url = "", $type = '')
{
    return imageService()->getImage($url, $type);
}

// ************ image function end ***************


// ************ date function end ***************

function get_date($date, $format = 'd-m-Y')
{
    return Carbon::parse($date)->translatedFormat($format);
}

function get_time($date, $format = 'h:i A')
{
    return Carbon::parse($date)->translatedFormat($format);
}

function getDateTime($date, $format = 'd-m-Y h:i A')
{
    return Carbon::parse($date)->translatedFormat($format);
}

// ************ date function end ***************


function apiObject($arrey, $newObj = null, $data = null)
{
    $temp = [];
    foreach ($arrey as $arr) {
        if ($newObj != null) {
            if ($data != null) {
                $temp[] = $arr->$newObj($data);
            } else {
                $temp[] = $arr->$newObj();
            }
        } else {
            if ($data != null) {
                $temp[] = $arr->apiObject($data);
            } else {
                $temp[] = $arr->apiObject();
            }
        }
    }
    return $temp;
}

function getCountries()
{
    return Cache::remember('getContries', 1440, function () { // 1440/60 = 1 day
        return Country::get();
    });

    //fore clear cashe
    // Cache::forget('getContries'); 
}

function getStates($country_id = 101)
{
    if (empty($country_id)) {
        $country_id = 101;
    }
    return Cache::remember('getStates', 1440, function () use ($country_id) { // 1440/60 = 1 day
        return State::where('country_id', $country_id)->get();
    });

    //fore clear cashe
    // Cache::forget('getStates'); 

}

function getCities($state_id = 12)
{
    if (empty($state_id)) {
        $state_id = 12;
    }
    return City::where('state_id', $state_id)->get();
}

function generateUniqueSlug($model, $name, $field = 'slug', $business_id = null)
{
    $slug = Str::slug($name);
    $originalSlug = $slug;
    $i = 1;

    while (
        $model::where($field, $slug)
        ->when($business_id, fn($q) => $q->where('business_id', $business_id))
        ->exists()
    ) {
        $slug = $originalSlug . '-' . $i;
        $i++;
    }

    return $slug;
}

// function generateUniqueSlug($model, $name, $field = 'slug', $business_id = null)
// {
//     $slug = Str::slug($name);
//     $originalSlug = $slug;
//     $i = 1;

//     $data = $model::where($field, $slug);
//     if ($business_id != null) {
//         $data = $data->where('business_id', $business_id);
//     }
//     $data = $data->exists();

//     while ($data) {
//         $slug = $originalSlug . '-' . $i;
//         $i++;
//     }

//     return $slug;
// }

// =============== Business functions start ================
function getBusinessId()
{
    return Auth::user()->business_id;
}

function getBusinessSettings($business_id = null, $forceRefresh = false)
{
    if ($business_id == null) {
        $business_id = getBusinessId();
    }

    if (!$forceRefresh && session()->has('business_settings') && session('business_settings_id') == $business_id) {
        $sessSettings = session('business_settings');
        if (!property_exists($sessSettings, 'is_lucky_draw')) {
            $sessSettings->is_lucky_draw = false;
        }
        if (!property_exists($sessSettings, 'is_quotation_charge_manual')) {
            $sessSettings->is_quotation_charge_manual = false;
        }
        if (!property_exists($sessSettings, 'is_create_quotation_on_inquiry')) {
            $sessSettings->is_create_quotation_on_inquiry = false;
        }
        if (!property_exists($sessSettings, 'is_login_required')) {
            $sessSettings->is_login_required = false;
        }
        if (!property_exists($sessSettings, 'charge_per_quotation')) {
            $sessSettings->charge_per_quotation = 0.10;
        }
        if (!property_exists($sessSettings, 'is_order_charge_manual')) {
            $sessSettings->is_order_charge_manual = false;
        }
        if (!property_exists($sessSettings, 'charge_place_order_on_website')) {
            $sessSettings->charge_place_order_on_website = 0.00;
        }
        if (!property_exists($sessSettings, 'charge_place_order_on_pos')) {
            $sessSettings->charge_place_order_on_pos = 0.00;
        }
        if (!property_exists($sessSettings, 'is_show_contact')) {
            $sessSettings->is_show_contact = true;
        }
        if (!property_exists($sessSettings, 'is_private')) {
            $sessSettings->is_private = true;
        }
        if (!property_exists($sessSettings, 'theme_setting')) {
            $sessSettings->theme_setting = config('theme.default_theme_setting', []);
        }
        return $sessSettings;
    }

    $setting = BusinessSetting::where('business_id', $business_id)->first();
    if ($setting) {
        $data = $setting->getBusinessSettingObject();
    } else {
        $data = [
            'is_appointment_system' => false,
            'is_appointment_with_department' => false,
            'is_appointment_price_required' => false,
            'allow_appointment_product_selection' => false,
            'is_appointment_creadit_diduct_manual' => false,
            'deduct_credit_per_customer_appointment' => 1,
            'deduct_credit_per_self_appointment' => 1,
            'is_ecommerce_system' => false,
            'is_label' => false,
            'is_share_products_to_business' => false,
            'is_product_import_export' => false,
            'is_service_system' => false,
            'is_show_contact' => true,
            'is_lucky_draw' => false,
            'is_quotation_charge_manual' => false,
            'is_create_quotation_on_inquiry' => false,
            'is_login_required' => false,
            'charge_per_quotation' => 0.10,
            'is_order_charge_manual' => false,
            'charge_place_order_on_website' => 0.00,
            'charge_place_order_on_pos' => 0.00,
            'credit' => 0,
            'is_verified' => false,
            'is_private' => true,
            'accept_online_payments' => false,
            'payment_method' => 'manual_upi',
            'online_payment_method' => 'manual_upi',
            'upi_id' => null,
            'razorpay_key_id' => null,
            'razorpay_key_secret' => null,
            'theme_setting' => config('theme.default_theme_setting', []),
        ];
    }
    $settingObj = (object)$data;
    session(['business_settings' => $settingObj, 'business_settings_id' => $business_id]);
    return $settingObj;
}

function getBusinessCategory()
{
    return Cache::rememberForever('BusinessCategory', function () { // 1440/60 = 1 day
        return BusinessCategory::where('status', 'active')->get();
    });
}

function getProductCategory($business_id = null, $onlyParent = false)
{
    if ($business_id == null && Auth::check()) {
        $business_id = getBusinessId();
    }

    if ($business_id) {
        $cacheKey = "business_{$business_id}_product_categories" . ($onlyParent ? '_parent' : '');
        return Cache::remember($cacheKey, 600, function () use ($business_id, $onlyParent) {
            $query = Category::with('parent:id,name')
                ->where('status', 'active')
                ->where('type', 'Products')
                ->where('business_id', $business_id);

            if ($onlyParent) {
                $query->whereNull('parent_id');
            }

            return $query->orderBy('sort_order', 'asc')->get();
        });
    }
    return collect();
}

function getServiceCategory($business_id = null)
{
    if ($business_id == null && Auth::check()) {
        $business_id = getBusinessId();
    }

    if ($business_id) {
        return Cache::remember("business_{$business_id}_service_categories", 600, function () use ($business_id) {
            return Category::with('parent:id,name')
                ->where('status', 'active')
                ->where('type', 'Services')
                ->where('business_id', $business_id)
                ->orderBy('sort_order', 'asc')
                ->get();
        });
    }
    return collect();
}

function isBusinessOpen($business_id = null)
{
    if ($business_id == null) {
        $business_id = getBusinessId();
    }
    $day = Carbon::now()->format('l');
    $time = Carbon::now()->format('H:i:s');
    $businessTiming = BusinessTiming::where('day', $day)->where('business_id', $business_id)->where('start_time', '<=', $time)->where('end_time', '>=', $time)->first();
    if ($businessTiming) {
        return true;
    }
    return false;
}

function isExpertAvailable($expert_id = null, $business_id = null)
{
    return app(\App\Services\ExpertService::class)->isExpertAvailable(
        $expert_id ? (int) $expert_id : null,
        $business_id ? (int) $business_id : null
    );
}

function updateBusinessSeo($bussinessId)
{
    $business = Business::select('id', 'name', 'address', 'business_category_id', 'country_id', 'state_id', 'city_id', 'area', 'pincode')
        ->with([
            'businessCategory',
            'country',
            'state',
            'city'
        ])
        ->find($bussinessId);

    $catname = '';
    if (isset($business->businessCategory) && !empty($business->businessCategory->name)) {
        $catname = $business->businessCategory->name;
    }

    $description = '';
    $keyword = '';
    if ($business) {
        if (isset($business->city) && !empty($business->city->name)) {
            $keyword .= $catname . ' in ' . $business->city->name . ', ';
            $keyword .= 'Top ' . $catname . ' in ' . $business->city->name . ', ';
            $keyword .= 'Best ' . $catname . ' in ' . $business->city->name . ', ';
        }

        if (isset($business->area) && !empty($business->area->area_name)) {
            $keyword .= 'Best ' . $catname . ' near ' . $business->area . ', ';
            $keyword .= $business->name . ' ' . $catname . ' ' . $business->area . ', ';
            $keyword .= 'Top ' . $catname . ' in ' . $business->area . ', ';
            $keyword .= 'nearby  ' . $catname . ' in ' . $business->area . ', ';

            $description = "Explore trusted {$catname} services in {$business->area}";
            $description .= !empty($business->city->name) ? " {$business->city->name}" : "";
            $description .= ". ";
        }

        $description .= "{$business->name} offers professional and reliable {$catname} solutions near you.";
    }

    Business::where('id', $bussinessId)->update([
        'seo_description' => $description,
        'seo_keyword' => $keyword,
    ]);
}

// =============== Business functions end ================

// =============== Appoinmenter functions start ================


function generateTimeSlots($startTime, $endTime, $date, $interval, $bookedArray)
{
    return app(\App\Services\ExpertService::class)->generateTimeSlots(
        (string) $startTime,
        (string) $endTime,
        $date,
        (int) $interval,
        (array) $bookedArray
    );
}

function getExpertTiming($id, $date, $appoinment_id = null, $getBusinessId = null)
{
    return app(\App\Services\ExpertService::class)->getExpertTiming(
        (int) $id,
        $date,
        $appoinment_id ? (int) $appoinment_id : null,
        $getBusinessId ? (int) $getBusinessId : null
    );
}


// =============== Appoinmenter functions end ================

// =============== geo location info functions start ================


function getLatLongOnAddress($address)
{
    $apiKey = ''; //get your api key from https://opencagedata.com/
    $client = new Client();

    if (empty($apiKey)) {
        $response = $client->get('https://nominatim.openstreetmap.org/search', [
            'query' => [
                'q' => $address,
                'format' => 'json',
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (!empty($data)) {
            return [
                'latitude' => $data[0]['lat'],
                'longitude' => $data[0]['lon'],
            ];
        }
    } else {
        $response = $client->get('https://api.opencagedata.com/geocode/v1/json', [
            'query' => [
                'q' => $address,
                'key' => $apiKey,
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if (!empty($data['results'])) {
            $location = $data['results'][0]['geometry'];
            return [
                'latitude' => $location['lat'],
                'longitude' => $location['lng'],
            ];
        }
    }
    return ['error' => 'Unable to fetch coordinates'];
}



// =============== geo location info functions end ================




// ==============================================
//      Frontend functions Start 
// ==============================================



// ==============================================
//      Frontend functions end 
// ==============================================

function generateQRCodeBase64($data)
{
    // $options = new QROptions([
    //     'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    //     'eccLevel' => QRCode::ECC_L,
    //     'scale' => 10,
    // ]);

    $myOptions = new QROptions([
        'version'    => 10,
        'outputType' => QROutputInterface::GDIMAGE_PNG,
        'eccLevel'   => EccLevel::H,
    ]);

    $qrPng = (new QRCode($myOptions))->render($data);
    return $qrPng;
    // echo "<img src='$qrPng' />";
}


function businessSticker($qrdata, $text)
{
    $baseImagePath = public_path('assets/images/stiker/poster.png');
    $qrImagePath = public_path('assets/images/stiker/qr.png');

    // Load base and QR images
    $base = imagecreatefrompng($baseImagePath);
    // $qr = imagecreatefrompng($qrImagePath);

    $qr = imagecreatefromstring(base64_decode(str_replace('data:image/png;base64,', '', generateQRCodeBase64($qrdata))));

    // Resize QR to 700x700
    $qrResized = imagecreatetruecolor(650, 650);
    imagealphablending($qrResized, false);
    imagesavealpha($qrResized, true);
    imagecopyresampled($qrResized, $qr, 0, 0, 0, 0, 650, 650, imagesx($qr), imagesy($qr));

    // Center QR
    $bgWidth = imagesx($base);
    $qrX = intval(($bgWidth - 650) / 2);
    $qrY = 700; // Adjust vertically
    imagecopy($base, $qrResized, $qrX, $qrY, 0, 0, 650, 650);

    //add text
    $fontPath = public_path('assets/images/stiker/Roboto-ExtraBold.ttf'); // Must exist
    $maxFontSize = 110;
    $minFontSize = 10;

    $imageWidth = imagesx($base);
    $imageHeight = imagesy($base);

    $textColor = imagecolorallocate($base, 255, 255, 255); // Set text color

    // Start with max size and shrink if needed
    $fontSize = $maxFontSize;

    do {
        $bbox = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textWidth = abs($bbox[2] - $bbox[0]);
        $textHeight = abs($bbox[7] - $bbox[1]);
        if ($textWidth <= $imageWidth - 40) {
            break;
        }
        $fontSize -= 2;
    } while ($fontSize >= $minFontSize);

    // Final X/Y position (centered)
    $textX = intval(($imageWidth - $textWidth) / 2);
    $textY = 340; // Adjust based on your design layout

    // Draw text
    imagettftext($base, $fontSize, 0, $textX, $textY, $textColor, $fontPath, $text);

    // Convert to base64
    ob_start();
    imagepng($base);
    $imageData = ob_get_clean();
    $base64 = base64_encode($imageData);
    $base64Image = 'data:image/png;base64,' . $base64;
    // echo "<img src='$base64Image' height='600' />";
    // dd($base64Image);

    // Cleanup
    imagedestroy($base);
    imagedestroy($qr);
    imagedestroy($qrResized);

    // Return as JSON or use directly in a view
    return $base64Image;
}

/**
 * Centered Global Coupon Validation Logic
 * 
 * @param string $couponCode
 * @param string $planType (subscription, product, service, appointment)
 * @param float $orderAmount
 * @return array
 */
function validateCoupon($couponCode, $planType, $orderAmount)
{
    try {
        $currentBusinessId = getBusinessId();

        if (empty($couponCode)) {
            return ['success' => false, 'message' => 'Coupon code is required.'];
        }

        $coupon = Coupon::where('code', $couponCode)
            ->where('status', 'active')
            ->first();

        if (!$coupon) {
            return ['success' => false, 'message' => 'Invalid or expired coupon code.'];
        }

        // 1. Check Dates
        $now = Carbon::now()->startOfDay();
        if ($now->lt($coupon->start_date) || $now->gt($coupon->end_date)) {
            return ['success' => false, 'message' => 'This coupon is not valid at this time.'];
        }

        // 2. Check Applicability
        $applicableFor = is_array($coupon->applicable_for) ? $coupon->applicable_for : ['all'];
        if (!in_array('all', $applicableFor) && !in_array($planType, $applicableFor)) {
            $typeLabel = $planType === 'credit' ? 'credit recharge' : $planType . ' plans';
            return ['success' => false, 'message' => 'This coupon is not applicable for ' . $typeLabel . '.'];
        }

        // 3. Check Specific Business
        if ($coupon->is_for_specific_business) {
            $allowedBusinesses = is_array($coupon->business_ids) ? $coupon->business_ids : [];

            // Cast to string for strict comparison safety if IDs are mixed types, though usually int
            // But let's just use in_array which is loose by default, or better, ensure types match.
            // JSON decode might make them strings or ints.

            if (!in_array($currentBusinessId, $allowedBusinesses)) {
                return ['success' => false, 'message' => 'This coupon is not valid for your business account.'];
            }
        }

        // 4. Check Global Usage Limit
        if ($coupon->usage_type != 'unlimited' && $coupon->usage_count >= $coupon->usage_limit) {
            return ['success' => false, 'message' => 'This coupon global usage limit has been reached.'];
        }

        // 5. Check Per-Business Usage Limit
        if ($coupon->is_limit_per_business) {
            $currentBusinessId = getBusinessId();
            // Assuming you have a way to count usage per business, e.g., from Purchase/Order table
            // Here we assume a 'purchases' or 'orders' relationship on Coupon or a separate query
            // You might need to import the Order/Purchase model if not already
            // For example:
            $userUsageCount = Purchase::where('coupon_code', $couponCode)
                ->where('business_id', $currentBusinessId)
                ->where('status', 'paid')
                ->count();

            if ($userUsageCount >= $coupon->usage_limit_per_business) {
                return ['success' => false, 'message' => 'You have reached the usage limit for this coupon.'];
            }
        }

        // 6. Check Minimum Purchase
        if ($orderAmount < $coupon->min_purchase) {
            return ['success' => false, 'message' => 'Minimum order amount of ₹' . number_format($coupon->min_purchase, 2) . ' is required to use this coupon.'];
        }

        // Calculate Discount
        $discountAmount = 0;
        if ($coupon->discount_type == 'flat') {
            $discountAmount = min($coupon->discount_value, $orderAmount);
        } else {
            $calcDiscount = ($orderAmount * $coupon->discount_value) / 100;
            if ($coupon->max_discount > 0) {
                $discountAmount = min($calcDiscount, $coupon->max_discount);
            } else {
                $discountAmount = $calcDiscount;
            }
        }

        return [
            'success' => true,
            'message' => 'Coupon applied successfully.',
            'coupon' => $coupon,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $orderAmount - $discountAmount)
        ];
    } catch (\Exception $e) {
        return ['success' => false, 'message' => 'Error validating coupon: ' . $e->getMessage()];
    }
}

function currencyFormat($amount)
{
    return '₹' . number_format($amount, 2);
}

function currencySymbol()
{
    return '₹';
}

// ==================== for POS ===========================

function getPosBusinessId()
{
    return app(\App\Services\PosSessionService::class)->getPosBusinessId();
}

function getPosBusinessDetails()
{
    return app(\App\Services\PosSessionService::class)->getPosBusinessDetails();
}

function getPosUserBusinesses()
{
    return app(\App\Services\PosSessionService::class)->getPosUserBusinesses();
}

function syncPosSessionData($user, $businessId = null)
{
    app(\App\Services\PosSessionService::class)->syncAll($user, $businessId);
}

function clearPosSessionData()
{
    app(\App\Services\PosSessionService::class)->clearPosSession();
}

/**
 * Check if the authenticated user has a specific POS permission.
 * Uses session-cached permissions for efficiency.
 */
function checkPosPermission($permission)
{
    return app(\App\Services\PosSessionService::class)->checkPosPermission($permission);
}

/**
 * Get allowed POS order statuses for the authenticated user.
 *
 * @return array
 */
function getPosAllowedOrderStatuses(): array
{
    return app(\App\Services\PosSessionService::class)->getPosAllowedOrderStatuses();
}

/**
 * Check if the authenticated POS user has access to a specific order status.
 *
 * @param string $status
 * @return bool
 */
function checkPosOrderStatusAccess(string $status): bool
{
    return app(\App\Services\PosSessionService::class)->checkPosOrderStatusAccess($status);
}

/**
 * Get allowed order status variants for SQL whereIn queries.
 * Handles capitalization and spacing variations gracefully.
 *
 * @param array|null $statuses
 * @return array
 */
function getPosAllowedOrderStatusQueryVariants(?array $statuses = null): array
{
    $statuses = $statuses ?? getPosAllowedOrderStatuses();
    $variants = [];
    foreach ($statuses as $st) {
        $stLow = strtolower($st);
        $variants[] = $stLow;
        $variants[] = ucfirst($stLow);
        $variants[] = ucwords(str_replace('_', ' ', $stLow));
    }
    return array_values(array_unique(array_filter($variants)));
}

/**
 * Check if the authenticated user has a specific business permission.
 * Uses session-cached permissions for efficiency.
 */
function checkBusinessPermission($permission)
{
    $user = Auth::user();
    if (!$user) {
        return false;
    }

    if ($user->role === 'Business') {
        return true;
    }

    $permissions = session('business_permissions');
    if ($permissions === null) {
        $user->syncBusinessPermissionsToSession();
        $permissions = session('business_permissions', []);
    }

    if (isset($permissions['all_access']) && $permissions['all_access']) {
        return true;
    }

    $businessPermissions = $permissions['business_permission'] ?? [];
    return !empty($businessPermissions[$permission]);
}

function getSiteSetting()
{
    return Cache::rememberForever('site_setting', function () {
        return \App\Models\SiteSetting::first();
    });
}

function encryptId($id)
{
    return str_replace(['+', '/', '='], ['-', '_', ''], encrypt($id));
}

function decryptId($value)
{
    try {
        $base64 = str_replace(['-', '_'], ['+', '/'], $value);
        return decrypt($base64);
    } catch (\Exception $e) {
        abort(404);
    }
}

if (!function_exists('numberToIndianWords')) {
    function numberToIndianWords($amount)
    {
        $number = (float)$amount;
        $no = floor($number);
        $point = round(($number - $no) * 100);
        $digits_length = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            0 => '',
            1 => 'One',
            2 => 'Two',
            3 => 'Three',
            4 => 'Four',
            5 => 'Five',
            6 => 'Six',
            7 => 'Seven',
            8 => 'Eight',
            9 => 'Nine',
            10 => 'Ten',
            11 => 'Eleven',
            12 => 'Twelve',
            13 => 'Thirteen',
            14 => 'Fourteen',
            15 => 'Fifteen',
            16 => 'Sixteen',
            17 => 'Seventeen',
            18 => 'Eighteen',
            19 => 'Nineteen',
            20 => 'Twenty',
            30 => 'Thirty',
            40 => 'Forty',
            50 => 'Fifty',
            60 => 'Sixty',
            70 => 'Seventy',
            80 => 'Eighty',
            90 => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $curr_no = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($curr_no) {
                $counter = count($str);
                $hundred = ($counter == 1 && isset($str[0]) && $str[0]) ? ' and ' : '';
                $str[] = ($curr_no < 21) ? $words[$curr_no] . ' ' . $digits[$counter] . ' ' . $hundred
                    : $words[floor($curr_no / 10) * 10] . ' ' . $words[$curr_no % 10] . ' ' . $digits[$counter] . ' ' . $hundred;
            } else {
                $str[] = '';
            }
        }
        $Rupees = implode('', array_reverse($str));
        $paise = ($point > 0 && isset($words[$point])) ? $words[$point] . ' Paise' : (($point > 0) ? ($words[floor($point / 10) * 10] . ' ' . $words[$point % 10]) . ' Paise' : '');
        $result = ($Rupees ? trim($Rupees) . ' Rupees ' : '') . ($paise ? 'And ' . trim($paise) . ' ' : '') . 'only';
        return preg_replace('/\s+/', ' ', trim($result));
    }
}

if (!function_exists('getYoutubeId')) {
    function getYoutubeId($url)
    {
        if (empty($url)) {
            return null;
        }
        preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|win/|user/[^/]+/|embed/|shorts/|watch\?v?=)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match);
        return $match[1] ?? null;
    }
}

if (!function_exists('getYoutubeThumbnail')) {
    function getYoutubeThumbnail($url)
    {
        $id = getYoutubeId($url);
        return $id ? "https://img.youtube.com/vi/$id/hqdefault.jpg" : null;
    }
}

if (!function_exists('cleanHtml')) {
    /**
     * Sanitize HTML input by stripping malicious script tags, event handlers, and javascript protocols.
     *
     * @param string|null $html
     * @return string|null
     */
    function cleanHtml(?string $html): ?string
    {
        if (empty($html)) {
            return $html;
        }

        // Remove dangerous executable tags and their inner content
        $clean = preg_replace('/<(script|iframe|object|embed|applet|form)\b[^>]*>(.*?)<\/\1>/is', '', $html);
        $clean = preg_replace('/<(script|iframe|object|embed|applet|form)\b[^>]*\/?>/is', '', $clean);

        // Strip inline JavaScript event handlers (e.g. onload=, onclick=, onerror=)
        $clean = preg_replace('/\bon[a-z]+\s*=\s*(["\']).*?\1/is', '', $clean);
        $clean = preg_replace('/\bon[a-z]+\s*=\s*[^>\s]+/is', '', $clean);

        // Strip javascript: and vbscript: URI schemes in attributes
        $clean = preg_replace('/\b(href|src|action)\s*=\s*(["\'])\s*(javascript|vbscript):[^"\']*\2/is', '', $clean);

        return $clean;
    }
}

if (!function_exists('isGoogleLoginEnabled')) {
    /**
     * Check if Google login is enabled.
     *
     * @return bool
     */
    function isGoogleLoginEnabled(): bool
    {
        return true;
    }
}

