<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\POS\PosAuthController;
use App\Http\Controllers\POS\PosDashboardController;
use App\Http\Controllers\POS\PosSaleController;
use App\Http\Controllers\POS\PosOrderController;

Route::group(['middleware' => 'web'], function () {
    Route::get('/login', [PosAuthController::class, 'showLogin'])->name('pos.login');
    Route::post('/login', [PosAuthController::class, 'login'])->name('pos.login.submit');
    Route::get('/logout', [PosAuthController::class, 'logout'])->name('pos.logout');

    Route::group(['middleware' => 'pos'], function () {
        Route::get('/', function() { return redirect()->route('pos.dashboard'); });
        Route::get('/dashboard', [PosDashboardController::class, 'index'])->name('pos.dashboard');

        Route::get('/new-sale', [PosSaleController::class, 'index'])->name('pos.sale.index');
        Route::get('/search-products', [PosSaleController::class, 'searchProducts'])->name('pos.sale.search');
        Route::post('/place-order', [PosSaleController::class, 'store'])->name('pos.sale.store');
        Route::get('/orders', [PosOrderController::class, 'index'])->name('pos.order.index');
        Route::get('/order/{id}', [PosOrderController::class, 'show'])->name('pos.order.details');

        Route::get('/quotations', [\App\Http\Controllers\POS\PosQuotationController::class, 'index'])->name('pos.quotation.index');
        Route::post('/quotations', [\App\Http\Controllers\POS\PosQuotationController::class, 'store'])->name('pos.quotation.store');
        Route::get('/quotation/{id}', [\App\Http\Controllers\POS\PosQuotationController::class, 'show'])->name('pos.quotation.details');
        Route::post('/quotation/convert/{id}', [\App\Http\Controllers\POS\PosQuotationController::class, 'convertToOrder'])->name('pos.quotation.convert');
        Route::post('/quotation/cancel/{id}', [\App\Http\Controllers\POS\PosQuotationController::class, 'cancel'])->name('pos.quotation.cancel');

        Route::get('/inventory', [\App\Http\Controllers\POS\PosInventoryController::class, 'index'])->name('pos.inventory.index');
        Route::post('/inventory/update-stock', [\App\Http\Controllers\POS\PosInventoryController::class, 'updateStock'])->name('pos.inventory.update');
    });
});
