<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API V2 Routes (Future Expansion)
|--------------------------------------------------------------------------
|
| Prefix: /api/v2
| Name Prefix: api.v2.
|
*/

Route::get('/ping', function () {
    return response()->json([
        'success' => true,
        'status_code' => 200,
        'message' => 'API V2 endpoint is live.',
        'data' => [
            'version' => 'v2',
            'status' => 'active',
        ],
    ]);
})->name('ping');
