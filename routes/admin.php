<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\BusinessController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\DashboarController;
use App\Http\Controllers\Admin\LagelPagesController;
use App\Http\Controllers\Admin\BusinessCategoryController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\PurchaseHistoryController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\CreditHistoryController;
use App\Http\Controllers\Admin\LogController;
use App\Http\Controllers\Admin\VisitorController;

Route::name('admin.')->group(function () {
    Route::controller(CommonController::class)->group(function () {
        Route::post('getCities', 'getCities')->name('getCities');
    });
    Route::middleware('web', 'guest:admin')->group(function () {

        Route::controller(AuthController::class)->group(function () {
            Route::get('login', 'index')->name('login');
            Route::post('login', 'store')->name('login.store')->middleware('throttle:6,1');
        });
    });

    Route::middleware(['web', 'admin'])->group(function () {
        Route::controller(DashboarController::class)->group(function () {
            Route::get('dashboard', 'index')->name('dashboard');
        });

        Route::controller(LogController::class)->prefix('logs')->name('logs.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('download', 'download')->name('download');
            Route::post('clear', 'clear')->name('clear');
        });


        Route::controller(UsersController::class)->group(function () {
            Route::post('user/change-password/{id}', 'changePassword')->name('user.changePassword');
            Route::get('user/{id}/login-as', 'loginAs')->name('user.login-as');
        });
        Route::resource('user', UsersController::class);
        Route::resource('faq', FaqController::class);
        Route::get('purchase-history/invoice/{id}', [PurchaseHistoryController::class, 'downloadInvoice'])->name('purchase-history.invoice');
        Route::resource('purchase-history', PurchaseHistoryController::class);

        Route::controller(CreditHistoryController::class)->prefix('credit-history')->name('credit-history.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('summary', 'businessSummary')->name('summary');
            Route::get('metrics', 'getSummaryMetrics')->name('metrics');
            Route::get('business-history/{businessId}', 'getBusinessHistory')->name('business-history');
        });

        Route::controller(TransactionController::class)->prefix('transactions')->name('transactions.')->group(function () {
            Route::get('pending', 'pending')->name('pending');
            Route::get('show/{id}', 'show')->name('show');
            Route::post('approve/{id}', 'approve')->name('approve');
            Route::post('reject/{id}', 'reject')->name('reject');
        });

        Route::resource('business', BusinessController::class);
        Route::post('blog/upload-image', [BlogController::class, 'uploadImage'])->name('blog.uploadImage');
        Route::resource('blog', BlogController::class);
        Route::controller(BusinessController::class)->group(function () {
            Route::get('business/pending/list', 'pendingBusinesses')->name('business.pendings');
            Route::post('business/change/status', 'changeBusinessStatus')->name('business.change.status');
        });

        Route::controller(VisitorController::class)->prefix('visitors')->name('visitors.')->group(function () {
            Route::get('/', 'index')->name('index');
        });

        Route::resource('businesscategory', BusinessCategoryController::class);
        Route::get('coupon/get-businesses', [CouponController::class, 'getBusinesses'])->name('coupon.getBusinesses');
        Route::get('coupon/usage-history/{id}', [CouponController::class, 'usageHistory'])->name('coupon.usageHistory');
        Route::resource('coupon', CouponController::class);
        Route::controller(BusinessController::class)->group(function () {
            Route::post('business/setting/{id}', 'systemSettingUpdate')->name('business.systemsetting.update');
        });

        Route::controller(LagelPagesController::class)->group(function () {
            Route::get('lagel-pages', 'index')->name('lagel-pages');
            Route::get('lagel-pages/{id}', 'edit')->name('lagel-pages.edit');
            Route::post('lagel-pages/{id}', 'update')->name('lagel-pages.update');
        });

        Route::controller(SettingController::class)->group(function () {
            Route::get('setting/profile', 'profile')->name('setting.profile');
            Route::post('setting/profile/{id}', 'profileUpdate')->name('setting.profile.update');
        });

        Route::controller(SiteSettingController::class)->group(function () {
            Route::get('site-setting', 'index')->name('site-setting.index');
            Route::post('site-setting/update', 'update')->name('site-setting.update');
        });



        Route::controller(AuthController::class)->group(function () {
            Route::get('logout', 'destroy')->name('logout');
        });
    });
});
