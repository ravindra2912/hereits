<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\AuthController;

Route::prefix('v1')->group(function () {
    route::controller(AuthController::class)->group(function () {
		Route::post('/login', 'login');
		Route::post('/registration', 'register');
		Route::post('/forgot-password', 'forgotPassword');
		Route::post('/reset-password', 'ResetPassword');
	});

    Route::group(['middleware' => ['auth:api']], function () {
        route::controller(AuthController::class)->group(function () {
            Route::post('/logout', 'logout');
        });
    });
});



// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:api');
