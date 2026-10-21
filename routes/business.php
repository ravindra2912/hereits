<?php

use App\Http\Middleware\Admin;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Business\AuthController;
use App\Http\Controllers\Business\PaymentController;
use App\Http\Controllers\Business\SettingController;
use App\Http\Controllers\Business\DashboarController;
use App\Http\Controllers\Business\ExpertController;
use App\Http\Controllers\Business\CustomerController;
use App\Http\Controllers\Business\CustomerRoleController;
use App\Http\Controllers\Business\OfferController;
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
use App\Http\Controllers\Business\StateDeliveryChargeController;

Route::name('business.')->group(function () {
    Route::middleware('web', 'guest')->group(function () {

        Route::controller(AuthController::class)->group(function () {
            Route::get('login', 'index')->name('login');
            Route::post('login', 'store')->name('login.store')->middleware('throttle:6,1');
        });
    });

    Route::middleware(['web', 'business'])->group(function () {
        Route::controller(DashboarController::class)->group(function () {
            Route::get('dashboard', 'index')->name('dashboard');
            Route::get('analytics', 'analyticsPageView')->name('analytics');
            Route::get('visitors', [\App\Http\Controllers\Business\VisitorController::class, 'index'])->name('visitors');
            Route::prefix('offers')->name('offer.')->group(function () {
                Route::get('/', [OfferController::class, 'index'])->name('index');
                Route::get('/search-items', [OfferController::class, 'searchItems'])->name('search-items');
                Route::post('/', [OfferController::class, 'store'])->name('store');
                Route::get('/{id}', [OfferController::class, 'show'])->name('show');
                Route::post('/{id}/update', [OfferController::class, 'update'])->name('update');
                Route::post('/{id}/status-toggle', [OfferController::class, 'toggleStatus'])->name('status-toggle');
                Route::delete('/{id}', [OfferController::class, 'destroy'])->name('destroy');
            });
            Route::get('influencer', 'influencer')->name('influencer');
            Route::post('dashboard-analytics', 'analytics')->name('dashboard.analytics');
        });

        Route::middleware(['checkModule:product'])->group(function () {
            Route::post('product/image-store', [ProductController::class, 'storeImage'])->name('product.image.store');
            Route::post('product/image-reorder', [ProductController::class, 'reorderImages'])->name('product.image.reorder');
            Route::delete('product/image-delete/{id}', [ProductController::class, 'deleteImage'])->name('product.image.delete');
            Route::get('product/inventory', [ProductController::class, 'inventory'])->name('product.inventory');
            Route::post('product/update-quantity', [ProductController::class, 'updateQuantity'])->name('product.update-quantity');
            Route::get('product/export', [ProductController::class, 'export'])->name('product.export');
            Route::post('product/import', [ProductController::class, 'import'])->name('product.import');
            Route::get('product/export-stock-price', [ProductController::class, 'exportStockPrice'])->name('product.export-stock-price');
            Route::post('product/import-stock-price', [ProductController::class, 'importStockPrice'])->name('product.import-stock-price');
            Route::post('product/status-toggle', [ProductController::class, 'statusToggle'])->name('product.status-toggle');
            Route::get('product/{id}/detail', [ProductController::class, 'showDetail'])->name('product.showDetail');
            Route::resource('product', ProductController::class);
            Route::get('product-share', [\App\Http\Controllers\Business\ProductShareController::class, 'index'])->name('product.share.index');
            Route::get('product-share/search-businesses', [\App\Http\Controllers\Business\ProductShareController::class, 'searchBusinesses'])->name('product.share.search_businesses');
            Route::post('product-share', [\App\Http\Controllers\Business\ProductShareController::class, 'store'])->name('product.share.store');
            Route::post('product-share/status-toggle', [\App\Http\Controllers\Business\ProductShareController::class, 'statusToggle'])->name('product.share.status-toggle');
            Route::delete('product-share/{id}', [\App\Http\Controllers\Business\ProductShareController::class, 'destroy'])->name('product.share.destroy');
            Route::get('product-share/manage/{id}', [\App\Http\Controllers\Business\ProductShareController::class, 'manage'])->name('product.share.manage');
            Route::post('product-share/manage/{id}/action', [\App\Http\Controllers\Business\ProductShareController::class, 'action'])->name('product.share.manage.action');
            Route::post('product-share/manage/{id}/sync-categories', [\App\Http\Controllers\Business\ProductShareController::class, 'syncCategories'])->name('product.share.manage.sync_categories');

            Route::post('product-category/status-toggle', [\App\Http\Controllers\Business\ProductCategoryController::class, 'statusToggle'])->name('product-category.status-toggle');
            Route::post('product-category/reorder', [\App\Http\Controllers\Business\ProductCategoryController::class, 'reorder'])->name('product-category.reorder');
            Route::resource('product-category', \App\Http\Controllers\Business\ProductCategoryController::class);
            Route::post('label/status-toggle', [\App\Http\Controllers\Business\LabelController::class, 'statusToggle'])->name('label.status-toggle');
            Route::post('label/reorder', [\App\Http\Controllers\Business\LabelController::class, 'reorder'])->name('label.reorder');
            Route::resource('label', \App\Http\Controllers\Business\LabelController::class);
            Route::resource('order', \App\Http\Controllers\Business\OrderController::class);
            Route::post('order/update-status/{id}', [\App\Http\Controllers\Business\OrderController::class, 'updateStatus'])->name('order.update-status');


            // Home Management Routes
            Route::controller(\App\Http\Controllers\Business\HomeManagementController::class)->group(function () {
                Route::get('home-management', 'index')->name('home-management');

                // Layouts Routes
                Route::get('home-management/layouts', 'layoutsIndex')->name('home-management.layouts');
                Route::get('home-management/layouts/search-items', 'layoutsSearchItems')->name('home-management.layouts.search-items');
                Route::post('home-management/layouts/store', 'layoutsStore')->name('home-management.layouts.store');
                Route::get('home-management/layouts/{id}/edit', 'layoutsEdit')->name('home-management.layouts.edit');
                Route::post('home-management/layouts/{id}/update', 'layoutsUpdate')->name('home-management.layouts.update');
                Route::post('home-management/layouts/update-order', 'layoutsUpdateOrder')->name('home-management.layouts.update-order');
                Route::post('home-management/layouts/{id}/toggle-status', 'layoutsToggleStatus')->name('home-management.layouts.toggle-status')->whereNumber('id');
                Route::post('home-management/layouts/item/{id}/toggle-status', 'layoutsItemToggleStatus')->name('home-management.layouts.item.toggle-status')->whereNumber('id');
                Route::delete('home-management/layouts/{id}', 'layoutsDestroy')->name('home-management.layouts.destroy');

                // Settings Routes (Business Home Popups)
                Route::get('home-management/settings', 'settingsIndex')->name('home-management.settings');
                Route::get('home-management/google-sheets-setup', 'googleSheetsSetupHelp')->name('home-management.google-sheets-setup');
                Route::post('home-management/settings/store', 'settingsStore')->name('home-management.settings.store');
                Route::get('home-management/settings/{id}/edit', 'settingsEdit')->name('home-management.settings.edit');
                Route::post('home-management/settings/{id}/update', 'settingsUpdate')->name('home-management.settings.update');
                Route::post('home-management/settings/{id}/toggle-status', 'settingsToggleStatus')->name('home-management.settings.toggle-status');
                Route::delete('home-management/settings/{id}', 'settingsDestroy')->name('home-management.settings.destroy');

            });

            // Campaign Leads Routes
            Route::controller(\App\Http\Controllers\Business\CampaignLeadController::class)->group(function () {
                Route::get('home-management/leads', 'index')->name('home-management.leads');
                Route::post('home-management/leads/store', 'store')->name('home-management.leads.store');
                Route::get('home-management/leads/{id}/edit', 'edit')->name('home-management.leads.edit');
                Route::post('home-management/leads/{id}/update', 'update')->name('home-management.leads.update');
                Route::post('home-management/leads/{id}/recall-webhook', 'recallWebhook')->name('home-management.leads.recall-webhook');
                Route::delete('home-management/leads/{id}', 'destroy')->name('home-management.leads.destroy');
            });

            // Product Plan Routes
            Route::controller(ProductPlanController::class)->group(function () {
                Route::get('product-plans', 'index')->name('product.plans');
                Route::post('product-plans/buy', 'buy')->name('product.plans.buy');
            });
        });
        Route::resource('banner', BannerController::class);

        Route::middleware(['checkModule:service'])->group(function () {
            Route::post('service/status-toggle', [ServiceController::class, 'statusToggle'])->name('service.status-toggle');
            Route::resource('service', ServiceController::class);
            Route::post('service-category/status-toggle', [\App\Http\Controllers\Business\ServiceCategoryController::class, 'statusToggle'])->name('service-category.status-toggle');
            Route::post('service-category/reorder', [\App\Http\Controllers\Business\ServiceCategoryController::class, 'reorder'])->name('service-category.reorder');
            Route::resource('service-category', \App\Http\Controllers\Business\ServiceCategoryController::class);

            // Service Plan Routes
            Route::controller(\App\Http\Controllers\Business\ServicePlanController::class)->group(function () {
                Route::get('service-plans', 'index')->name('service.plans');
                Route::post('service-plans/buy', 'buy')->name('service.plans.buy');
            });
        });
        Route::post('gallery/status-toggle', [GalleryController::class, 'statusToggle'])->name('gallery.status-toggle');
        Route::resource('gallery', GalleryController::class);

        Route::post('blog/upload-image', [\App\Http\Controllers\Business\BlogController::class, 'uploadImage'])->name('blog.uploadImage');
        Route::post('blog/status-toggle', [\App\Http\Controllers\Business\BlogController::class, 'statusToggle'])->name('blog.status-toggle');
        Route::resource('blog', \App\Http\Controllers\Business\BlogController::class);

        Route::name('appointment.')->group(function () {
            Route::get('customers/export', [CustomerController::class, 'export'])->name('customers.export');
            Route::resource('customers', CustomerController::class)->only(['index', 'show', 'edit', 'update']);
            Route::post('customers/{id}/update', [CustomerController::class, 'update'])->name('customers.update-post');
            Route::get('customers/{id}/profile', [CustomerController::class, 'profile'])->name('customers.profile');
            Route::post('customers/{id}/assign-role', [CustomerController::class, 'assignRole'])->name('customers.assign-role');

            Route::prefix('customer-roles')->name('customer-roles.')->group(function () {
                Route::get('/', [CustomerRoleController::class, 'index'])->name('index');
                Route::post('/', [CustomerRoleController::class, 'store'])->name('store');
                Route::get('/{id}', [CustomerRoleController::class, 'show'])->name('show');
                Route::post('/{id}/update', [CustomerRoleController::class, 'update'])->name('update');
                Route::post('/{id}/status-toggle', [CustomerRoleController::class, 'toggleStatus'])->name('status-toggle');
                Route::delete('/{id}', [CustomerRoleController::class, 'destroy'])->name('destroy');
            });
        });

        Route::name('appointment.')->middleware(['checkModule:appointment'])->group(function () {
            Route::controller(AppointmentBookingController::class)->group(function () {
                Route::get('bookings/export', 'export')->name('bookings.export');
                Route::get('bookings/pending/list', 'gatPendingBookings')->name('bookings.pending');
                Route::get('bookings/search-product', 'searchProduct')->name('bookings.search-product');
                Route::post('bookings/change-status', 'changeStatus')->name('bookings.change.status');
                Route::post('bookings/get-expert', 'getExpertByDepartment')->name('bookings.get.expert');
                Route::post('bookings/get-expert-timing', 'getExpertTiming')->name('bookings.get.expert.timing');
            });
            Route::resource('bookings', AppointmentBookingController::class);
            Route::resource('department', AppointmentDepartmentController::class);

            Route::resource('expert', ExpertController::class);
            Route::controller(ExpertController::class)->group(function () {
                Route::post('expert/status-toggle', 'statusToggle')->name('expert.status-toggle');
                Route::post('expert/check-user', 'checkUser')->name('expert.check-user');
                Route::get('expert/timing/{id}', 'timing')->name('expert.timing');
                Route::post('expert/timing/{id}', 'timingStore')->name('expert.timing.store');
                Route::post('expert/Timingdestroy', 'TimingDestroy')->name('expert.timing.destroy');
            });
        });

        Route::controller(SettingController::class)->group(function () {
            Route::get('setting/profile', 'profile')->name('setting.profile');
            Route::post('setting/profile/{id}', 'profileUpdate')->name('setting.profile.update');

            Route::get('setting/business/profile', 'businessProfile')->name('setting.business');
            Route::get('setting/business/seo', 'businessSeo')->name('setting.business.seo');
            Route::get('setting/business/share', 'businessShare')->name('setting.business.share');
            Route::get('setting/business/configuration', 'businessConfiguration')->name('setting.business.configuration');
            Route::get('setting/business/about-us', 'businessAboutUs')->name('setting.business.about_us');

            Route::post('setting/business/profile/{id}', 'businessUpdate')->name('setting.business.update');
            Route::post('setting/business/seo/{id}', 'seoUpdate')->name('setting.seo.update');

            Route::get('setting/business/timings', 'businessTiming')->name('setting.business.timing');
            Route::post('setting/business/timings/add', 'businessTimingStore')->name('setting.business.timing.add');
            Route::post('setting/business/timings/remove', 'businessTimingSestroy')->name('setting.business.timing.remove');
            Route::get('setting/business/theme', 'businessTheme')->name('setting.business.theme');
            Route::post('setting/business/theme', 'themeUpdate')->name('setting.business.theme.update');
            Route::post('setting/business/theme/reset', 'themeReset')->name('setting.business.theme.reset');
            Route::post('setting/business/system-setting', 'systemSettingUpdate')->name('setting.systemsetting.update');
            Route::get('switch-business/{id}', 'switchBusiness')->name('switchBusiness');

            Route::post('setting/business/service-limit/buy', 'serviceLimitBuy')->name('setting.business.servicelimit.buy');
        });

        Route::resource('setting/business/leagle-page', \App\Http\Controllers\Business\BusinessLeaglePageController::class)
            ->names([
                'index' => 'setting.business.leagle-page.index',
                'create' => 'setting.business.leagle-page.create',
                'store' => 'setting.business.leagle-page.store',
                'edit' => 'setting.business.leagle-page.edit',
                'update' => 'setting.business.leagle-page.update',
                'destroy' => 'setting.business.leagle-page.destroy',
            ]);

        Route::controller(StateDeliveryChargeController::class)->group(function () {
            Route::get('setting/business/state-charges', 'index')->name('setting.business.state-charges');
            Route::post('setting/business/state-charges', 'store')->name('setting.business.state-charges.store');
            Route::get('setting/business/state-charges/{id}/edit', 'edit')->name('setting.business.state-charges.edit');
            Route::post('setting/business/state-charges/{id}/update', 'update')->name('setting.business.state-charges.update');
            Route::delete('setting/business/state-charges/{id}', 'destroy')->name('setting.business.state-charges.destroy');
        });

        // Credit Routes
        Route::controller(CreditController::class)->group(function () {
            Route::get('credits', 'index')->name('credits');
            Route::get('credits/history', 'history')->name('credits.history');
            Route::get('credits/details', 'show')->name('credits.details');
            Route::post('credits/validate-coupon', 'validateCouponAjax')->name('credits.validate_coupon');
            Route::post('credits/buy', 'buy')->name('credits.buy');
        });

        // Lucky Draw Routes
        Route::controller(\App\Http\Controllers\Business\LuckyDrawController::class)->prefix('lucky-draw')->name('lucky-draw.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/store', 'store')->name('store');
            Route::get('/{id}', 'show')->name('show');
            Route::post('/{id}/update', 'update')->name('update');
            Route::post('/{id}/change-status', 'changeStatus')->name('change-status');
            Route::delete('/{id}', 'destroy')->name('destroy');
            Route::get('/{id}/source-prizes/{source_id}', 'getSourcePrizes')->name('source-prizes');
            Route::post('/{id}/import-prizes', 'importPrizes')->name('import-prizes');
            Route::post('/{id}/prize/store', 'storePrize')->name('prize.store');
            Route::delete('/{id}/prize/{prize_id}', 'deletePrize')->name('prize.delete');
            Route::post('/{id}/generate-entry', 'generateEntry')->name('generate-entry');
            Route::post('/{id}/entry/{entry_id}/update', 'updateEntry')->name('entry.update');
        });

        Route::controller(ProductPlanController::class)->group(function () {
            Route::get('product-plan', 'index')->name('product.plan');
            Route::get('product-plan/details/{id}', 'show')->name('product.plan.details');
            Route::post('product-plan/validate-coupon', 'validateCouponAjax')->name('product.plan.validate_coupon');
            Route::post('product-plan/buy', 'buy')->name('product.plan.buy');
        });

        Route::controller(ServicePlanController::class)->group(function () {
            Route::get('service-plan', 'index')->name('service.plan');
            Route::get('service-plan/details/{id}', 'show')->name('service.plan.details');
            Route::post('service-plan/validate-coupon', 'validateCouponAjax')->name('service.plan.validate_coupon');
            Route::post('service-plan/buy', 'buy')->name('service.plan.buy');
        });

        Route::controller(SubscriptionPlanController::class)->group(function () {
            Route::get('subscription', 'index')->name('subscription');
            Route::get('subscription/details/{id}', 'show')->name('subscription.details');
            Route::post('subscription/validate-coupon', 'validateCouponAjax')->name('subscription.validate_coupon');
            Route::post('subscription/buy', 'buy')->name('subscription.buy');
        });

        Route::controller(PurchaseHistoryController::class)->group(function () {
            Route::get('purchase-history', 'index')->name('purchase.history');
            Route::get('purchase-history/{id}', 'show')->name('purchase.history.detail');
            Route::get('purchase-history/invoice/{id}', 'downloadInvoice')->name('purchase.history.invoice');
        });

        Route::controller(PaymentController::class)->group(function () {
            Route::get('payment/{type}/{id}', 'Payment')->name('Payment');
            Route::post('payment/response', 'paymentResponse')->name('payment.response');
            Route::post('payment/razorpay-verify', 'razorpayVerify')->name('payment.razorpay.verify');
            Route::post('payment/upi-submit', 'upiPaymentSubmit')->name('payment.upi.submit');
        });

        // Store Management Routes
        Route::resource('role', \App\Http\Controllers\Business\RoleController::class);
        Route::post('staff/check-email', [\App\Http\Controllers\Business\StaffController::class, 'checkEmail'])->name('staff.check-email');
        Route::get('staff/{user_id}/history', [\App\Http\Controllers\Business\StaffController::class, 'history'])->name('staff.history');
        Route::resource('staff', \App\Http\Controllers\Business\StaffController::class);

        Route::controller(AuthController::class)->group(function () {
            Route::get('logout', 'destroy')->name('logout');
        });
    });
});
