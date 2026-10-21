<?php

use App\Http\Controllers\Front\DocumentController;
use Illuminate\Support\Facades\Route;

Route::get('/documents/domain-mapping/{slug?}', [DocumentController::class, 'domainMapping'])->name('documents.domain');
