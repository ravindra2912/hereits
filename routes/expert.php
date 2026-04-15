<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Expert\Auth\LoginController;
use App\Http\Controllers\Expert\DashboardController;

Route::middleware(['web'])->group(function () {

    Route::middleware('guest:expert')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('expert.login');
        Route::post('login', [LoginController::class, 'store'])->name('expert.login.store');
    });

    Route::middleware('expert')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('expert.logout');

        Route::controller(DashboardController::class)->name('expert.')->group(function () {
            Route::get('/', 'index')->name('dashboard');
            Route::post('status', 'updateStatus')->name('status.update');
            Route::get('history', 'history')->name('history');
        });

        Route::controller(App\Http\Controllers\Expert\AppointmentController::class)->prefix('appointments')->name('expert.appointments.')->group(function () {
            Route::get('create', 'create')->name('create');
            Route::post('store', 'store')->name('store');
            Route::post('get-timing', 'getExpertTiming')->name('get.timing');
        });

        Route::controller(App\Http\Controllers\Expert\ProfileController::class)->prefix('profile')->name('expert.profile.')->group(function () {
            Route::get('/', 'edit')->name('edit');
            Route::post('update', 'update')->name('update');
        });
    });
});
