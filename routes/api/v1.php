<?php

use App\Http\Controllers\Api\V1\ApiV1AppointmentController;
use App\Http\Controllers\Api\V1\ApiV1AuthController;
use App\Http\Controllers\Api\V1\ApiV1BusinessController;
use App\Http\Controllers\Api\V1\ApiV1CategoryController;
use App\Http\Controllers\Api\V1\ApiV1FavoriteController;
use App\Http\Controllers\Api\V1\ApiV1HomeController;
use App\Http\Controllers\Api\V1\ApiV1OrderController;
use App\Http\Controllers\Api\V1\ApiV1ProductController;
use App\Http\Controllers\Api\V1\ApiV1QuotationController;
use App\Http\Controllers\Api\V1\ApiV1ServiceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V1 Routes
|--------------------------------------------------------------------------
|
| Prefix: /api/v1
| Name Prefix: api.v1.
|
*/

// Public Authentication Routes (Rate-limited to prevent brute-force attacks)
Route::middleware('throttle:6,1')->controller(ApiV1AuthController::class)->group(function () {
    Route::post('/login', 'login')->name('login');
    Route::post('/register', 'register')->name('register');
    Route::post('/registration', 'register')->name('registration'); // Backward compatibility
    Route::post('/forgot-password', 'forgotPassword')->name('password.forgot');
    Route::post('/reset-password', 'resetPassword')->name('password.reset');
});

// Home Module Routes
Route::controller(ApiV1HomeController::class)->group(function () {
    Route::get('/home', 'index')->name('home');
});

// Category Module Routes
Route::controller(ApiV1CategoryController::class)->group(function () {
    Route::get('/categories', 'index')->name('categories');
    Route::get('/business/{business_id}/categories', 'businessCategories')->name('business.categories');
});

// Business Module Routes
Route::controller(ApiV1BusinessController::class)->group(function () {
    Route::get('/businesses', 'businesses')->name('businesses');
    Route::get('/business/{id}', 'businessDetail')->name('business.detail');
});

// Product Module Routes
Route::controller(ApiV1ProductController::class)->group(function () {
    Route::get('/business/{business_id}/products', 'index')->name('business.products');
    Route::get('/business/product/{id}', 'show')->name('business.product.detail');
    Route::get('/product/{id}', 'show')->name('product.detail');
});

// Service Module Routes
Route::controller(ApiV1ServiceController::class)->group(function () {
    Route::get('/business/{business_id}/services', 'index')->name('business.services');
    Route::get('/business/service/{id}', 'show')->name('business.service.detail');
    Route::get('/service/{id}', 'show')->name('service.detail');
});

// Appointment Public Routes
Route::controller(ApiV1AppointmentController::class)->group(function () {
    Route::get('/business/{business_id}/experts', 'getExperts')->name('business.experts');
    Route::get('/expert/{id}', 'show')->name('expert.detail');
    Route::get('/business/{business_id}/expert/{id}', 'show')->name('business.expert.detail');
    Route::post('/expert-timing', 'getTiming')->name('expert.timing');
});

// Protected Routes (Require Passport Token)
Route::middleware(['auth:api'])->group(function () {
    Route::controller(ApiV1AuthController::class)->group(function () {
        Route::get('/me', 'profile')->name('profile.me');
        Route::get('/profile', 'profile')->name('profile');
        Route::get('/user/profile', 'profile')->name('user.profile');
        Route::post('/user/profile/update', 'updateProfile')->name('user.profile.update');
        Route::post('/user/profile/update-password', 'updatePassword')->name('user.profile.update_password');
        Route::post('/logout', 'logout')->name('logout');
    });

    // Favorite & Follow Module Routes
    Route::controller(ApiV1FavoriteController::class)->group(function () {
        Route::post('/toggle-favorite', 'toggle')->name('favorite.toggle_alias');
        Route::post('/favorite/toggle', 'toggle')->name('favorite.toggle');
        Route::post('/favorites/toggle', 'toggle')->name('favorites.toggle');
        Route::get('/user/favorites', 'index')->name('user.favorites');
        Route::get('/favorites', 'index')->name('favorites');
    });

    // Orders Module Routes
    Route::controller(ApiV1OrderController::class)->group(function () {
        Route::get('/user/orders', 'index')->name('user.orders');
        Route::get('/user/orders/{id}', 'show')->name('user.orders.show');
        Route::post('/user/orders/review', 'review')->name('user.orders.review');
    });

    // Appointments Module Routes
    Route::controller(ApiV1AppointmentController::class)->group(function () {
        Route::get('/my-appointments', 'myAppointments')->name('user.appointments');
        Route::get('/appointment/{id}', 'appointmentDetail')->name('user.appointments.detail');
        Route::post('/book-appointment', 'bookAppointment')->name('user.appointments.book');
        Route::post('/appointment/cancel', 'cancelAppointment')->name('user.appointments.cancel');
    });

    // Quotations Module Routes
    Route::controller(ApiV1QuotationController::class)->group(function () {
        Route::get('/user/quotations', 'index')->name('user.quotations');
        Route::get('/user/quotations/{id}', 'show')->name('user.quotations.show');
    });
});

