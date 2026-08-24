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
use App\Services\ImageService;
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

// ************ image function start ***************

function fileRemoveStorage($imageObject)
{
    if ($imageObject != null) {
        return (new ImageService)->removeImage($imageObject);
    }
}

function fileUploadStorage($imageObject, $directory = "", $width = "", $hieght = "", $converto = "webp")
{
    return (new ImageService)->storeImage($imageObject, $directory, $width, $hieght, $converto);
}

function getImage($url = "", $type = '')
{
    return (new ImageService)->getImage($url, $type);
}

function getYoutubeId($url)
{
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|shorts/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return $match[1] ?? null;
}

function getGalleryVideoUrl($url)
{
    $id = getYoutubeId($url);
    if ($id) {
        return "https://www.youtube.com/watch?v=$id";
    }
    return $url;
}

function getYoutubeThumbnail($url)
{
    $id = getYoutubeId($url);
    return $id ? "https://img.youtube.com/vi/$id/hqdefault.jpg" : null;
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
    return Cache::remember('getCities_' . $state_id, 1440, function () use ($state_id) {
        return City::where('state_id', $state_id)->get();
    });
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

function renderStatusControl(string $actionUrl, string $currentStatus, $recordId, bool $canUpdate = true, array $labels = []): string
{
    $activeValue = $labels['active_value'] ?? 'active';
    $inactiveValue = $labels['inactive_value'] ?? 'in-active';
    $activeLabel = $labels['active_label'] ?? 'Active';
    $inactiveLabel = $labels['inactive_label'] ?? 'Inactive';

    $isActive = $currentStatus === $activeValue;

    if (! $canUpdate) {
        $badgeClass = $isActive ? 'bg-success' : 'bg-danger';
        $badgeLabel = $isActive ? $activeLabel : $inactiveLabel;

        return '<span class="badge rounded-pill ' . $badgeClass . ' px-3 py-1 small">' . e($badgeLabel) . '</span>';
    }

    $switchId = 'status-switch-' . $recordId;

    return '<form action="' . e($actionUrl) . '" method="POST" class="d-inline-flex justify-content-center formaction status-switch-form" data-action="">' .
        csrf_field() .
        '<input type="hidden" name="status" value="' . e($currentStatus) . '">' .
        '<div class="form-check form-switch text-switch mb-0">' .
        '<input type="checkbox" class="form-check-input status-switch-input" id="' . e($switchId) . '" data-on="' . e($activeLabel) . '" data-off="' . e($inactiveLabel) . '" data-active-value="' . e($activeValue) . '" data-inactive-value="' . e($inactiveValue) . '" ' . ($isActive ? 'checked' : '') . '>' .
        '</div>' .
        '</form>';
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

function getBusinessSettings($business_id = null)
{
    if ($business_id == null) {
        $business_id = getBusinessId();
    }
    $setting = BusinessSetting::where('business_id', $business_id)->first();
    if ($setting) {
        $data = $setting->getBusinessSettingObject();
    } else {
        $data = [
            'is_appointment_system' => false,
            'is_appointment_with_department' => false,
            'is_appointment_price_required' => false,
            'is_appointment_creadit_diduct_manual' => false,
            'deduct_credit_per_customer_appointment' => 1,
            'deduct_credit_per_self_appointment' => 1,
            'is_ecommerce_system' => false,
            'is_product_import_export' => false,
            'is_service_system' => false,
            'credit' => 0,
            'is_verified' => false,
            'visibility' => 'public'
        ];
    }
    return (object)$data;
}

function getBusinessCategory()
{
    return Cache::rememberForever('BusinessCategory', function () { // 1440/60 = 1 day
        return BusinessCategory::where('status', 'active')->get();
    });
}

function getProductCategory($business_id = null)
{
    if ($business_id == null && Auth::check()) {
        $business_id = getBusinessId();
    }

    if ($business_id) {
        return Category::where('status', 'active')
            ->where('type', 'Products')
            ->where('business_id', $business_id)
            ->orderBy('sort_order', 'asc')
            ->get();
    }
    return collect();
}

function getServiceCategory($business_id = null)
{
    if ($business_id == null && Auth::check()) {
        $business_id = getBusinessId();
    }

    if ($business_id) {
        return Category::where('status', 'active')
            ->where('type', 'Services')
            ->where('business_id', $business_id)
            ->orderBy('sort_order', 'asc')
            ->get();
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
    $res['status'] = 'close';
    $res['data'] = null;

    if ($expert_id != null) {
        $expert = Expert::select('id', 'is_appointment_book_with_time_slot')->where('id', $expert_id)->first();
        $currentBooking = AppointmentBooking::select('id', 'token_number', 'user_id', 'expert_id', 'user_name', 'user_contact', 'slot_start_time', 'slot_end_time', 'booking_date', 'status')
            ->where('booking_date', Carbon::now()->format('Y-m-d'))
            ->where('expert_id', $expert_id)
            ->where('status', 'in_progress');
        if ($expert->is_appointment_book_with_time_slot) {
            $currentBooking = $currentBooking->orderBy('slot_start_time', 'asc');
        } else {
            $currentBooking = $currentBooking->orderBy('token_number', 'asc');
        }
        $currentBooking = $currentBooking->first();

        if ($currentBooking) {
            $res['status'] = 'open';
            $res['data'] = $currentBooking;
        } else {
            $day = Carbon::now()->format('l');
            $time = Carbon::now()->format('H:i:s');
            $businessTiming = BusinessTiming::where('day', $day)
                ->where('expert_id', $expert_id)
                ->where('start_time', '<=', $time)
                ->where('end_time', '>=', $time)
                ->first();
            if ($businessTiming) {
                $res['status'] = 'open';
            } else {
                $businessTiming = BusinessTiming::select('id', 'start_time')
                    ->where('day', $day)
                    ->where('expert_id', $expert_id)
                    ->where('start_time', '>=', $time)
                    ->first();
                if ($businessTiming) {
                    $res['status'] = 'break';
                    $res['data'] = $businessTiming;
                }
            }
        }
    }
    return $res;
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
            return ['success' => false, 'message' => 'This coupon is not applicable for ' . $planType . ' plans.'];
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
    return Auth::guard('pos')->user()->business_id;
}

/**
 * Check if the authenticated user has a specific POS permission.
 * Uses session-cached permissions for efficiency.
 */
function checkPosPermission($permission)
{
    $permissions = session('permissions', []);
    if (isset($permissions['all_access']) && $permissions['all_access']) {
        return true;
    }

    // Check if permission exists and is truthy
    // Flattened structure check (the keys in pos_permission are the permission codes)
    $posPermissions = $permissions['pos_permission'] ?? [];
    return !empty($posPermissions[$permission]);
}

/**
 * Check if the authenticated user has a specific Business panel permission.
 * 
 * @param string $module The main module/setting (e.g. 'customers', 'appointments', 'store_management')
 * @param string|null $submodule The sub-feature (e.g. 'department', 'role')
 * @param string|null $action The action to check ('view', 'add', 'update', 'delete')
 * @return bool
 */
function checkBusinessPermission($module, $submodule = null, $action = null)
{
    $user = Auth::user();
    if (!$user) {
        return false;
    }

    // Owner has full access to everything in the business panel
    if ($user->role === 'Business') {
        return true;
    }

    // Ensure session permissions are populated
    if (!session()->has('permissions')) {
        $user->syncPermissionsToSession();
    }

    $permissions = session('permissions', []);
    if (isset($permissions['all_access']) && $permissions['all_access']) {
        return true;
    }

    // Check if business access is globally allowed
    $businessAccess = $permissions['business_access'] ?? false;
    if (!$businessAccess) {
        return false;
    }

    $businessPermissions = $permissions['business_permissions'] ?? [];
    if (empty($businessPermissions)) {
        return false;
    }

    // 1. If only module is checked (e.g. general module like 'customers' or 'analytics')
    if ($submodule === null && $action === null) {
        if (isset($businessPermissions[$module])) {
            return $businessPermissions[$module] === 'yes';
        }
        return false;
    }

    // 2. If it's a module with sub-features
    if (isset($businessPermissions[$module])) {
        $moduleData = $businessPermissions[$module];

        // Ensure module has general access allowed
        if (!isset($moduleData['access']) || $moduleData['access'] !== 'yes') {
            return false;
        }

        // If checking sub-module with specific action
        if ($submodule !== null && $action !== null) {
            $actions = $moduleData[$submodule] ?? [];
            return is_array($actions) && in_array($action, $actions);
        }
    }

    return false;
}

function getSiteSetting()
{
    return Cache::rememberForever('site_setting', function () {
        return \App\Models\SiteSetting::first();
    });
}
