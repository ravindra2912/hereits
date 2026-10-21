<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes Configuration & Version Management
|--------------------------------------------------------------------------
|
| Here is where API routes are registered. Routes in this file are
| assigned the "api" middleware group and prefixed with "/api".
| Versioning is cleanly managed via dedicated route files under routes/api/.
|
*/

// API Version 1
Route::prefix('v1')
    ->as('api.v1.')
    ->group(base_path('routes/api/v1.php'));

// API Version 2
Route::prefix('v2')
    ->as('api.v2.')
    ->group(base_path('routes/api/v2.php'));
