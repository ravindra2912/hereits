<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\ApiV1AuthController;
use App\Http\Controllers\Api\V1\ApiV1HomeController;
use App\Http\Controllers\Api\V1\ApiV1BusinessController;
use App\Http\Controllers\Api\V1\ApiV1AppointmentController;
use App\Http\Controllers\Api\V1\ApiV1AccountController;
use App\Http\Controllers\Api\V1\ApiV1ChatController;
use App\Http\Controllers\Api\V1\ApiV1RagbotController;
use App\Http\Controllers\Api\V1\ApiV1SupportTicketController;
use App\Http\Controllers\Api\V1\ApiV1LocationController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::controller(ApiV1AuthController::class)->group(function () {
        Route::post('/login', 'login');
        Route::post('/google-login', 'googleLogin');
        Route::post('/registration', 'register');
        Route::post('/forgot-password', 'forgotPassword');
        Route::post('/reset-password', 'ResetPassword');
    });

    // Public Home & Business Routes
    Route::controller(ApiV1HomeController::class)->group(function () {
        Route::get('/home', 'index');
        Route::get('/categories', 'categories');
        Route::get('/businesses', 'businesses');
    });

    Route::controller(ApiV1LocationController::class)->prefix('location')->group(function () {
        Route::get('/search-cities', 'searchCities');
    });

    Route::controller(ApiV1BusinessController::class)->prefix('business')->group(function () {
        Route::get('/{id}', 'show');
        Route::get('/{id}/services', 'services');
        Route::get('/{id}/products', 'products');
        Route::get('/{id}/reviews', 'reviews');
        Route::get('/product/{id}', 'productDetails');
        Route::get('/service/{id}', 'serviceDetails');
    });

    Route::controller(ApiV1AppointmentController::class)->group(function () {
        Route::get('/business/{businessId}/experts', 'experts');
        Route::get('/expert/{id}', 'expertDetails');
        Route::post('/expert-timing', 'getExpertTiming');
        Route::post('/book-appointment', 'bookAppointment');
    });

    Route::post('/chatbot/chat', [ApiV1RagbotController::class, 'ragChat']);

    // Passport Authenticated Routes
    Route::middleware(['auth:api'])->group(function () {
        Route::controller(ApiV1AuthController::class)->group(function () {
            Route::post('/logout', 'logout');
        });

        Route::controller(ApiV1HomeController::class)->group(function () {
            Route::post('/toggle-favorite', 'toggleFavorite');
        });

        Route::controller(ApiV1AppointmentController::class)->group(function () {
            Route::get('/my-appointments', 'myAppointments');
        });

        Route::controller(ApiV1AccountController::class)->prefix('user')->group(function () {
            Route::get('/profile', 'profile');
            Route::post('/profile/update', 'updateProfile');
            Route::post('/profile/update-password', 'updatePassword');
            Route::get('/favorites', 'favorites');
            Route::get('/orders', 'orders');
            Route::get('/orders/{id}', 'showOrder');
            Route::post('/orders/review', 'submitOrderReview');
        });

        Route::controller(ApiV1ChatController::class)->prefix('chat')->group(function () {
            Route::get('/conversations', 'conversations');
            Route::post('/conversations/start', 'startConversation');
            Route::get('/conversations/{conversationId}/messages', 'messages');
            Route::post('/conversations/{conversationId}/messages', 'sendMessage');
        });
    });

    Route::middleware(['auth:ai-agent'])->group(function () {
        Route::post('/tickets', [ApiV1SupportTicketController::class, 'store']);
    });
});
