<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\AccountController;
use App\Http\Controllers\Api\V1\ChatApiController;
use App\Http\Controllers\Api\V1\SupportTicketController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::controller(AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/registration', 'register');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'ResetPassword');
    });

    // Public Home & Business Routes
    Route::controller(HomeController::class)->group(function () {
        Route::get('/home', 'index');
        Route::get('/categories', 'categories');
        Route::get('/businesses', 'businesses');
    });

    Route::controller(\App\Http\Controllers\Api\V1\LocationApiController::class)->prefix('location')->group(function () {
        Route::get('/search-cities', 'searchCities');
    });

    Route::controller(BusinessController::class)->prefix('business')->group(function () {
        Route::get('/{id}', 'show');
        Route::get('/{id}/services', 'services');
        Route::get('/{id}/products', 'products');
        Route::get('/{id}/reviews', 'reviews');
        Route::get('/product/{id}', 'productDetails');
        Route::get('/service/{id}', 'serviceDetails');
    });

    Route::controller(AppointmentController::class)->group(function () {
        Route::get('/business/{businessId}/experts', 'experts');
        Route::post('/expert-timing', 'getExpertTiming');
        Route::post('/book-appointment', 'bookAppointment');
    });

    // Passport Authenticated Routes
    Route::middleware(['auth:api'])->group(function () {
        Route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout');
        });

        Route::controller(HomeController::class)->group(function () {
            Route::post('/toggle-favorite', 'toggleFavorite');
        });

        Route::controller(AppointmentController::class)->group(function () {
            Route::get('/my-appointments', 'myAppointments');
        });

        Route::controller(AccountController::class)->prefix('user')->group(function () {
            Route::get('/profile', 'profile');
            Route::post('/profile/update', 'updateProfile');
            Route::get('/favorites', 'favorites');
            Route::get('/orders', 'orders');
        });

        Route::controller(ChatApiController::class)->prefix('chat')->group(function () {
            Route::get('/conversations', 'conversations');
            Route::post('/conversations/start', 'startConversation');
            Route::get('/conversations/{conversationId}/messages', 'messages');
            Route::post('/conversations/{conversationId}/messages', 'sendMessage');
        });
    });

    Route::middleware(['auth:ai-agent'])->group(function () {
        Route::post('/tickets', [SupportTicketController::class, 'store']);
    });
});
