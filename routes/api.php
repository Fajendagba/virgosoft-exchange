<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - v1
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    require __DIR__ . '/v1/auth.php';
    require __DIR__ . '/v1/trading.php';
});