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
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\PurchaseHistoryController;
use App\Http\Controllers\Admin\SiteSettingController;
use App\Http\Controllers\Admin\TransactionController;

Route::name('admin.')->group(function () {
    Route::controller(CommonController::class)->group(function () {
        Route::post('getCities', 'getCities')->name('getCities');
    });
    Route::middleware('web', 'guest:admin')->group(function () {



        Route::controller(AuthController::class)->group(function () {
            Route::get('login', 'index')->name('login');
            Route::post('login', 'store')->name('login');
        });
    });



    Route::middleware(['web', 'admin'])->group(function () {
        Route::controller(DashboarController::class)->group(function () {
            Route::get('dashboard', 'index')->name('dashboard');
        });


        Route::controller(UsersController::class)->group(function () {
            Route::post('user/change-password/{id}', 'changePassword')->name('user.changePassword');
        });
        Route::resource('user', UsersController::class);
        Route::resource('faq', FaqController::class);
        Route::get('purchase-history/invoice/{id}', [PurchaseHistoryController::class, 'downloadInvoice'])->name('purchase-history.invoice');
        Route::resource('purchase-history', PurchaseHistoryController::class);

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
            Route::get('business/expired/list', 'expiredBusinesses')->name('business.expired');
            Route::post('business/change/status', 'changeBusinessStatus')->name('business.change.status');
        });



        Route::resource('businesscategory', BusinessCategoryController::class);
        Route::resource('plan', PlanController::class);
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
