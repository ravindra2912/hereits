<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POS\PosAuthController;
use App\Http\Controllers\POS\PosDashboardController;
use App\Http\Controllers\POS\PosSaleController;
use App\Http\Controllers\POS\PosOrderController;

Route::group(['middleware' => 'web'], function () {
    Route::get('/login', [PosAuthController::class, 'showLogin'])->name('pos.login');
    Route::post('/login', [PosAuthController::class, 'login'])->name('pos.login.submit')->middleware('throttle:6,1');
    Route::get('/logout', [PosAuthController::class, 'logout'])->name('pos.logout');

    Route::group(['middleware' => 'pos'], function () {
        Route::get('/', function() { return redirect()->route('pos.dashboard'); });
        Route::get('/dashboard', [PosDashboardController::class, 'index'])->name('pos.dashboard');
        Route::post('/switch-business', [PosAuthController::class, 'switchBusiness'])->name('pos.switch-business');

        Route::get('/new-sale', [PosSaleController::class, 'index'])->name('pos.sale.index');
        Route::get('/search-products', [PosSaleController::class, 'searchProducts'])->name('pos.sale.search');
        Route::post('/calculate-shipping', [PosSaleController::class, 'calculateShipping'])->name('pos.sale.calculate-shipping');
        Route::post('/place-order', [PosSaleController::class, 'store'])->name('pos.sale.store');
        Route::get('/orders', [PosOrderController::class, 'index'])->name('pos.order.index');
        Route::get('/order/search-product', [PosOrderController::class, 'searchProduct'])->name('pos.order.search-product');
        Route::get('/order/{id}', [PosOrderController::class, 'show'])->name('pos.order.details');
        Route::get('/order/{id}/edit', [PosOrderController::class, 'edit'])->name('pos.order.edit');
        Route::put('/order/{id}/update', [PosOrderController::class, 'update'])->name('pos.order.update');
        Route::post('/order/{id}/update-status', [PosOrderController::class, 'updateStatus'])->name('pos.order.update-status');
        Route::post('/order/{id}/update-assign-to', [PosOrderController::class, 'updateAssignTo'])->name('pos.order.update-assign-to');

        Route::get('/transactions', [\App\Http\Controllers\POS\PosTransactionController::class, 'index'])->name('pos.transaction.index');
        Route::get('/transactions/{id}', [\App\Http\Controllers\POS\PosTransactionController::class, 'show'])->name('pos.transaction.show');
        Route::post('/transactions/{id}/verify', [\App\Http\Controllers\POS\PosTransactionController::class, 'verify'])->name('pos.transaction.verify');

        Route::get('/inventory', [\App\Http\Controllers\POS\PosInventoryController::class, 'index'])->name('pos.inventory.index');
        Route::post('/inventory/update-stock', [\App\Http\Controllers\POS\PosInventoryController::class, 'updateStock'])->name('pos.inventory.update');

        // POS Quotations
        Route::controller(\App\Http\Controllers\POS\PosQuotationController::class)->group(function () {
            Route::get('/quotations', 'index')->name('pos.quotation.index');
            Route::get('/quotation/create', 'create')->name('pos.quotation.create');
            Route::post('/quotation', 'store')->name('pos.quotation.store');
            Route::get('/quotation/search-customer', 'searchCustomer')->name('pos.quotation.search-customer');
            Route::get('/quotation/search-product', 'searchProduct')->name('pos.quotation.search-product');
            Route::post('/quotation/calculate-shipping', 'calculateShipping')->name('pos.quotation.calculate-shipping');
            Route::get('/quotation/{id}', 'show')->name('pos.quotation.show');
            Route::get('/quotation/{id}/pdf', 'pdf')->name('pos.quotation.pdf');
            Route::get('/quotation/{id}/edit', 'edit')->name('pos.quotation.edit');
            Route::put('/quotation/{id}', 'update')->name('pos.quotation.update');
            Route::delete('/quotation/{id}', 'destroy')->name('pos.quotation.destroy');
            Route::post('/quotation/{id}/convert', 'convertToOrder')->name('pos.quotation.convert');
            Route::post('/quotation/{id}/duplicate', 'duplicate')->name('pos.quotation.duplicate');
            Route::post('/quotation/{id}/cancel', 'cancel')->name('pos.quotation.cancel');
            Route::post('/quotation/{id}/notify', 'notify')->name('pos.quotation.notify');
        });

        // POS Lucky Draw
        Route::controller(\App\Http\Controllers\POS\PosLuckyDrawController::class)->group(function () {
            Route::get('/lucky-draw', 'index')->name('pos.lucky-draw.index');
            Route::post('/lucky-draw/generate', 'generateEntry')->name('pos.lucky-draw.generate');
            Route::put('/lucky-draw/{id}', 'updateEntry')->name('pos.lucky-draw.update');
        });
    });
});
