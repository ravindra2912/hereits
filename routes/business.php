<?php

use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Business\AuthController;
use App\Http\Controllers\Business\PaymentController;
use App\Http\Controllers\Business\SettingController;
use App\Http\Controllers\Business\DashboarController;
use App\Http\Controllers\Business\ExpertController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\AppointmentBookingController;
use App\Http\Controllers\Business\AppointmentDepartmentController;
use App\Http\Controllers\Business\ProductController;
use App\Http\Controllers\Business\ProductPlanController;
use App\Http\Controllers\Business\BannerController;
use App\Http\Controllers\Business\ServiceController;
use App\Http\Controllers\Business\ServicePlanController;
use App\Http\Controllers\Business\SubscriptionPlanController;
use App\Http\Controllers\Business\CreditController;
use App\Http\Controllers\Business\PurchaseHistoryController;
use App\Http\Controllers\Business\GalleryController;

Route::name('business.')->group(function () {
    Route::middleware('web', 'guest')->group(function () {

        Route::controller(AuthController::class)->group(function () {
            Route::get('login', 'index')->name('login');
            Route::post('login', 'store')->name('login');
        });
    });

    Route::middleware(['web', 'business'])->group(function () {
        Route::controller(DashboarController::class)->group(function () {
            Route::get('dashboard', 'index')->name('dashboard');
            Route::get('analytics', 'analyticsPageView')->name('analytics')->middleware('checkBusinessPerm:analytics');
            Route::get('influencer', 'influencer')->name('influencer');
            Route::post('dashboard-analytics', 'analytics')->name('dashboard.analytics')->middleware('checkBusinessPerm:analytics');
        });

        Route::middleware(['checkModule:product'])->group(function () {
            Route::post('product/image-store', [ProductController::class, 'storeImage'])->name('product.image.store')->middleware('checkBusinessPerm:product,products,update');
            Route::post('product/image-reorder', [ProductController::class, 'reorderImages'])->name('product.image.reorder')->middleware('checkBusinessPerm:product,products,update');
            Route::delete('product/image-delete/{id}', [ProductController::class, 'deleteImage'])->name('product.image.delete')->middleware('checkBusinessPerm:product,products,update');
            Route::get('product/inventory', [ProductController::class, 'inventory'])->name('product.inventory')->middleware('checkBusinessPerm:product,products');
            Route::post('product/update-quantity', [ProductController::class, 'updateQuantity'])->name('product.update-quantity')->middleware('checkBusinessPerm:product,products');
            Route::get('product/export', [ProductController::class, 'export'])->name('product.export')->middleware('checkBusinessPerm:product,products');
            Route::post('product/import', [ProductController::class, 'import'])->name('product.import')->middleware('checkBusinessPerm:product,products');
            Route::resource('product', ProductController::class)->middleware('checkBusinessPerm:product,products');
            Route::post('product-category/reorder', [\App\Http\Controllers\Business\ProductCategoryController::class, 'reorder'])->name('product-category.reorder')->middleware('checkBusinessPerm:product,categories');
            Route::resource('product-category', \App\Http\Controllers\Business\ProductCategoryController::class)->middleware('checkBusinessPerm:product,categories');
            Route::resource('order', \App\Http\Controllers\Business\OrderController::class)->middleware('checkBusinessPerm:owner_only');
            Route::post('order/update-status/{id}', [\App\Http\Controllers\Business\OrderController::class, 'updateStatus'])->name('order.update-status')->middleware('checkBusinessPerm:owner_only');
            Route::post('order/update-customer/{id}', [\App\Http\Controllers\Business\OrderController::class, 'updateCustomer'])->name('order.update-customer')->middleware('checkBusinessPerm:owner_only');

            // Home Management Routes
            Route::controller(\App\Http\Controllers\Business\HomeManagementController::class)->group(function () {
                Route::get('home-management', 'index')->name('home-management');
                Route::post('home-management/add-category', 'addCategory')->name('home-management.add-category');
                Route::post('home-management/remove-category', 'removeCategory')->name('home-management.remove-category');
                Route::get('home-management/search-category', 'searchCategory')->name('home-management.search-category');
            })->middleware('checkBusinessPerm:home_management');

            // Product Plan Routes
            Route::controller(ProductPlanController::class)->group(function () {
                Route::get('product-plans', 'index')->name('product.plans');
                Route::post('product-plans/buy', 'buy')->name('product.plans.buy');
            })->middleware('checkBusinessPerm:owner_only');
        });
        Route::resource('banner', BannerController::class)->middleware('checkBusinessPerm:owner_only');

        Route::middleware(['checkModule:service'])->group(function () {
            Route::resource('service', ServiceController::class)->middleware('checkBusinessPerm:service,service_list');
            Route::post('service-category/reorder', [\App\Http\Controllers\Business\ServiceCategoryController::class, 'reorder'])->name('service-category.reorder')->middleware('checkBusinessPerm:service,categories');
            Route::resource('service-category', \App\Http\Controllers\Business\ServiceCategoryController::class)->middleware('checkBusinessPerm:service,categories');

            // Service Plan Routes
            Route::controller(\App\Http\Controllers\Business\ServicePlanController::class)->group(function () {
                Route::get('service-plans', 'index')->name('service.plans');
                Route::post('service-plans/buy', 'buy')->name('service.plans.buy');
            })->middleware('checkBusinessPerm:owner_only');
        });
        Route::resource('gallery', GalleryController::class)->middleware('checkBusinessPerm:store_management,gallery');

        Route::name('appointment.')->middleware(['checkModule:appointment'])->group(function () {
            Route::get('bookings/export', [AppointmentBookingController::class, 'export'])->name('bookings.export')->middleware('checkBusinessPerm:appointments,appointments');
            Route::resource('bookings', AppointmentBookingController::class)->middleware('checkBusinessPerm:appointments,appointments');
            Route::controller(AppointmentBookingController::class)->group(function () {
                Route::get('bookings/pending/list', 'gatPendingBookings')->name('bookings.pending');

                Route::post('bookings/change-status', 'changeStatus')->name('bookings.change.status');

                Route::post('bookings/get-expert', 'getExpertByDepartment')->name('bookings.get.expert');
                Route::post('bookings/get-expert-timing', 'getExpertTiming')->name('bookings.get.expert.timing');
            })->middleware('checkBusinessPerm:appointments,appointments');
            Route::resource('department', AppointmentDepartmentController::class)->middleware('checkBusinessPerm:appointments,department');
            Route::resource('customers', CustomerController::class)->middleware('checkBusinessPerm:customers');

            Route::resource('expert', ExpertController::class)->middleware('checkBusinessPerm:appointments,experts');
            Route::controller(ExpertController::class)->group(function () {
                Route::get('expert/timing/{id}', 'timing')->name('expert.timing');
                Route::post('expert/timing/{id}', 'timingStore')->name('expert.timing.store');
                Route::post('expert/Timingdestroy', 'TimingDestroy')->name('expert.timing.destroy');
            })->middleware('checkBusinessPerm:appointments,experts');
        });

        Route::controller(SettingController::class)->group(function () {
            Route::get('setting/profile', 'profile')->name('setting.profile');
            Route::post('setting/profile/{id}', 'profileUpdate')->name('setting.profile.update');

            Route::get('setting/business/profile', 'businessProfile')->name('setting.business')->middleware('checkBusinessPerm:owner_only');
            Route::get('setting/business/seo', 'businessSeo')->name('setting.business.seo')->middleware('checkBusinessPerm:owner_only');
            Route::get('setting/business/share', 'businessShare')->name('setting.business.share');
            Route::get('setting/business/configuration', 'businessConfiguration')->name('setting.business.configuration')->middleware('checkBusinessPerm:owner_only');
            Route::get('setting/business/about-us', 'businessAboutUs')->name('setting.business.about_us')->middleware('checkBusinessPerm:owner_only');

            Route::post('setting/business/profile/{id}', 'businessUpdate')->name('setting.business.update')->middleware('checkBusinessPerm:owner_only');
            Route::post('setting/business/seo/{id}', 'seoUpdate')->name('setting.seo.update')->middleware('checkBusinessPerm:owner_only');

            Route::get('setting/business/timings', 'businessTiming')->name('setting.business.timing')->middleware('checkBusinessPerm:store_management,timing');
            Route::post('setting/business/timings/add', 'businessTimingStore')->name('setting.business.timing.add')->middleware('checkBusinessPerm:store_management,timing');
            Route::post('setting/business/timings/remove', 'businessTimingSestroy')->name('setting.business.timing.remove')->middleware('checkBusinessPerm:store_management,timing');
            Route::post('setting/business/system-setting', 'systemSettingUpdate')->name('setting.systemsetting.update')->middleware('checkBusinessPerm:owner_only');
            Route::get('switch-business/{id}', 'switchBusiness')->name('switchBusiness');

            Route::post('setting/business/service-limit/buy', 'serviceLimitBuy')->name('setting.business.servicelimit.buy')->middleware('checkBusinessPerm:owner_only');
        });

        // Credit Routes
        Route::controller(CreditController::class)->group(function () {
            Route::get('credits', 'index')->name('credits');
            Route::get('credits/details', 'show')->name('credits.details');
            Route::post('credits/validate-coupon', 'validateCouponAjax')->name('credits.validate_coupon');
            Route::post('credits/buy', 'buy')->name('credits.buy');
        })->middleware('checkBusinessPerm:owner_only');

        Route::controller(ProductPlanController::class)->group(function () {
            Route::get('product-plan', 'index')->name('product.plan');
            Route::get('product-plan/details/{id}', 'show')->name('product.plan.details');
            Route::post('product-plan/validate-coupon', 'validateCouponAjax')->name('product.plan.validate_coupon');
            Route::post('product-plan/buy', 'buy')->name('product.plan.buy');
        })->middleware('checkBusinessPerm:owner_only');

        Route::controller(ServicePlanController::class)->group(function () {
            Route::get('service-plan', 'index')->name('service.plan');
            Route::get('service-plan/details/{id}', 'show')->name('service.plan.details');
            Route::post('service-plan/validate-coupon', 'validateCouponAjax')->name('service.plan.validate_coupon');
            Route::post('service-plan/buy', 'buy')->name('service.plan.buy');
        })->middleware('checkBusinessPerm:owner_only');

        Route::controller(SubscriptionPlanController::class)->group(function () {
            Route::get('subscription', 'index')->name('subscription');
            Route::get('subscription/details/{id}', 'show')->name('subscription.details');
            Route::post('subscription/validate-coupon', 'validateCouponAjax')->name('subscription.validate_coupon');
            Route::post('subscription/buy', 'buy')->name('subscription.buy');
        })->middleware('checkBusinessPerm:owner_only');

        Route::controller(PurchaseHistoryController::class)->group(function () {
            Route::get('purchase-history', 'index')->name('purchase.history');
            Route::get('purchase-history/{id}', 'show')->name('purchase.history.detail');
            Route::get('purchase-history/invoice/{id}', 'downloadInvoice')->name('purchase.history.invoice');
        })->middleware('checkBusinessPerm:owner_only');

        Route::controller(PaymentController::class)->group(function () {
            Route::get('payment/{type}/{id}', 'Payment')->name('Payment');
            Route::post('payment/response', 'paymentResponse')->name('payment.response');
            Route::post('payment/upi-submit', 'upiPaymentSubmit')->name('payment.upi.submit');
        });

        // Store Management Routes
        Route::resource('role', \App\Http\Controllers\Business\RoleController::class)->middleware('checkBusinessPerm:store_management,role');
        Route::post('staff/check-email', [\App\Http\Controllers\Business\StaffController::class, 'checkEmail'])->name('staff.check-email')->middleware('checkBusinessPerm:store_management,staff');
        Route::resource('staff', \App\Http\Controllers\Business\StaffController::class)->middleware('checkBusinessPerm:store_management,staff');

        Route::controller(AuthController::class)->group(function () {
            Route::get('logout', 'destroy')->name('logout');
        });
    });
});
