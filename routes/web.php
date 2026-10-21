<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Front\HomeController;
use App\Http\Controllers\Front\AccountController;
use App\Http\Controllers\Front\Business\BusinessController;
use App\Http\Controllers\Front\Business\AppointmentController;
use App\Http\Controllers\Front\Business\ProductController;
use App\Http\Controllers\Front\Business\CheckoutController;
use App\Http\Controllers\Front\Business\PaymentController;
use App\Http\Controllers\Front\Business\ServiceController;
use App\Http\Controllers\Front\Business\CommonController as BusinessCommonController;
use App\Http\Controllers\Front\BlogController;
use App\Http\Controllers\Front\LocationController;

Route::post('/set-location', [LocationController::class, 'setLocation'])->name('set-location');

Route::controller(BlogController::class)->name('blog.')->group(function () {
    Route::get('/blogs', 'index')->name('index');
    Route::get('/blog/{slug}', 'show')->name('detail');
});

// Route::get('test-email', function () {
//     // generateQRCode('sdsddsd');
//     $appointment_details = AppointmentBooking::query()
//         ->select('id', 'token_number', 'business_id', 'expert_id', 'user_id', 'user_name', 'user_contact', 'slot_start_time', 'slot_end_time', 'booking_date', 'note', 'status')
//         ->with([
//             'expert:id,expert_name,slug,is_appointment_book_with_time_slot',
//             'business:id,name,slug,address',
//             'user:id,first_name,email,notification_token'
//         ])
//         ->find(39);

//     Mail::to($appointment_details->user->email)->send(new AppointmentConfirmationMail($appointment_details));
//     echo 'success';
// });


Route::get('/sitemap.xml', [SitemapController::class, 'index']);

Route::get('/.well-known/assetlinks.json', function () {
    $path = public_path('.well-known/assetlinks.json');
    if (file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'application/json',
        ]);
    }
    return response()->json([], 404);
});

Route::controller(CommonController::class)->group(function () {
    Route::get('purchase/invoice/{id}', 'downloadInvoice')->name('purchase.invoice');
});
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::get('/why-join-with-us', 'whyJoinWithUs')->name('why-join-with-us');
    Route::get('/faq', 'faq')->name('faq');
    Route::get('/about-us', 'aboutUs')->name('aboutUs');
    Route::get('/contact-us', 'contactUs')->name('contactUs');
    Route::get('/privacy-policy', 'privacyPolicy')->name('privacyPolicy');
    Route::get('/term-and-condition', 'termAndCondition')->name('termAndCondition');
    Route::get('/copy-right', 'CopyRight')->name('CopyRight');
    Route::get('/cancellation-and-refund-policy', 'CancellationAndRefundPolicy')->name('CancellationAndRefundPolicy');
    Route::get('/vendor-policy', 'VendorPolicy')->name('VendorPolicy');
    Route::get('/businesses', 'businessList')->name('business-list');
    Route::get('/categories', 'categoriesList')->name('categories-list');
    Route::get('/global-search', 'globalSearch')->name('global-search');
    Route::post('/toggle-favorite', 'toggleFavorite')->name('toggle-favorite');
});

Route::controller(AppointmentController::class)->group(function () {
    Route::get('{business_slug}/experts', 'expertList')->name('expert.list');
    Route::get('{business_slug}/expert/{expert_slug?}', 'index')->name('expert');
    Route::post('{business_slug}/book-appointment', 'bookAppointment')->name('book.appointment');
    Route::post('{business_slug}/get-expert-timing', 'getExpertTiming')->name('get.expert.timing');
    Route::get('{business_slug}/expert/board/{expert_slug?}', 'board')->name('expert.board');
});


// User Authenticated Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'store')->name('login')->middleware('throttle:6,1');
    Route::post('Register', 'register')->name('register')->middleware('throttle:10,1');
    Route::post('register', 'register')->middleware('throttle:10,1');

    // Tenant-prefixed auth aliases (supports custom domain proxy workers)
    Route::post('{business_slug}/login', 'store')->middleware('throttle:6,1');
    Route::post('{business_slug}/Register', 'register')->middleware('throttle:10,1');
    Route::post('{business_slug}/register', 'register')->middleware('throttle:10,1');

    Route::get('/auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('/auth/google/callback', 'handleGoogleCallback')->name('auth.google.callback');


    Route::get('Register-business', 'registerBusinessView')->name('register.business');
    Route::post('Register-business', 'registerBusiness')->name('register.business.store')->middleware('throttle:10,1');

    Route::post('forgot-password', 'forgotPassword')->name('forgot.password')->middleware('throttle:6,1');
    Route::post('{business_slug}/forgot-password', 'forgotPassword')->middleware('throttle:6,1');
    Route::get('password-reset/{token}/{email}', 'ResetPasswordForm')->name('password.reset');
    Route::get('{business_slug}/password-reset/{token}/{email}', 'ResetPasswordForm');
    Route::post('password-reset', 'ResetPassword')->name('password.reset.update')->middleware('throttle:6,1');
    Route::post('{business_slug}/password-reset', 'ResetPassword')->middleware('throttle:6,1');
});

Route::middleware(['web', 'front'])->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('logout', 'destroy')->name('logout');
        Route::get('{business_slug}/logout', 'destroy');
    });

    Route::controller(AccountController::class)->name('account.')->prefix('account')->group(function () {
        Route::get('', 'index')->name('index');
        Route::get('user-profile', 'userProfile')->name('userprofile');
        Route::post('user-profile/update/{id}', 'userProfileUpdate')->name('userprofile.update');
        Route::get('chnage-password', 'changePassword')->name('changePassword');
        Route::post('chnage-password/update', 'changePasswordUpdate')->name('changePassword.update');

        //booking
        Route::get('bookings', 'booking')->name('booking');
        Route::get('get-bookings', 'getBookings')->name('get.booking');
        Route::get('booking/{id}', 'bookingDetails')->name('booking.details');
        Route::post('booking/cancel', 'bookingCancel')->name('booking.cancel');
        Route::post('booking/review', 'bookingReview')->name('booking.review');

        // Orders
        Route::get('orders', 'orders')->name('orders');
        Route::get('order/{id}', 'orderDetails')->name('order.details');

        // Quotations
        Route::get('quotations', 'quotations')->name('quotations');
        Route::get('quotation/{id}', 'quotationDetails')->name('quotation.details');

        // Favorites
        Route::get('favorites', 'favorites')->name('favorites');
        Route::post('favorites/remove', 'removeFavorite')->name('favorites.remove');
        Route::post('favorites/toggle', 'toggleFavorite')->name('favorites.toggle');
    });

    // Proxy fallback for tenant-prefixed account URLs
    Route::get('{business_slug}/account/{subpath?}', function ($business_slug, $subpath = null) {
        return redirect('account' . ($subpath ? '/' . $subpath : ''));
    })->where('subpath', '.*');
});

Route::post('favorite/toggle', [AccountController::class, 'toggleFavorite'])->name('favorite.toggle');
Route::post('{business_slug}/favorite/toggle', [AccountController::class, 'toggleFavorite']);

Route::controller(BusinessController::class)->group(function () {
    Route::post('{business_slug}/campaign-lead/submit', 'submitCampaignLead')->name('business.campaign-lead.submit');
    Route::get('{business_slug}', 'businessDetails')->name('business-details');
    Route::get('{business_slug}/legal/{type}', 'legalPageDetails')->name('business-legal-page');
    Route::get('{business_slug}/quotation/{quotation_no}', 'quotationDetails')->name('quotation-details');
    Route::get('{business_slug}/quotation/{quotation_no}/pdf', 'quotationPdf')->name('quotation-pdf');
    Route::post('{business_slug}/quotation/{quotation_no}/update-qty', 'updateQuotationQty')->name('quotation-update-qty');
    Route::get('{business_slug}/blogs', 'blogList')->name('business-blogs');
    Route::get('{business_slug}/blog/{blog_slug}', 'blogDetails')->name('business-blog-details');
});

Route::controller(BusinessCommonController::class)->group(function () {
    Route::get('{business_slug}/galleries', 'galleryList')->name('business-galleries');
    Route::get('{business_slug}/search', 'search')->name('business-search');
});

Route::controller(ServiceController::class)->group(function () {
    Route::get('{business_slug}/services', 'serviceList')->name('business-services');
    Route::get('{business_slug}/service/{service_slug}', 'serviceDetails')->name('service-details');
});

Route::controller(ProductController::class)->group(function () {
    Route::get('{business_slug}/product/{product_slug}', 'productDetails')->name('product-detail');
    Route::get('{business_slug}/products', 'businessProducts')->name('business-products');
});

Route::controller(CheckoutController::class)->group(function () {
    Route::get('{business_slug}/checkout', 'checkout')->name('checkout');
    Route::post('{business_slug}/place-order', 'placeOrder')->name('place-order');
    Route::post('{business_slug}/whatsapp-quotation', 'createWhatsAppQuotation')->name('whatsapp-quotation');
});

Route::controller(PaymentController::class)->group(function () {
    Route::get('payment/{type}/{source_id}', 'showPayment')->name('payment.show');
    Route::post('payment/{type}/{source_id}', 'submitPayment')->name('payment.submit');
    Route::post('payment/{type}/{source_id}/razorpay-verify', 'verifyRazorpayPayment')->name('payment.razorpay.verify');
    Route::get('payment/{type}/{source_id}/success', 'orderSuccess')->name('payment.order-success');
});

// Lucky Draw Front Scratch Routes
Route::controller(\App\Http\Controllers\Front\LuckyDrawScratchController::class)->group(function () {
    Route::get('{business_slug}/lucky-draw/scratch/{token}', 'scratch')->name('lucky-draw.scratch');
    Route::post('{business_slug}/lucky-draw/claim/{token}', 'claim')->name('lucky-draw.claim');
});

// require __DIR__ . '/auth.php';
