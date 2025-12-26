<?php

use App\Http\Controllers\API\v1\Trading\OrderController;
use App\Http\Controllers\API\v1\Trading\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| TRADING API Routes - v1
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:api'])->group(function () {
    Route::get('profile', ProfileController::class);

    Route::controller(OrderController::class)->prefix('orders')->group(function () {
        Route::get('', 'index');
        Route::get('me', 'userOrders');
        Route::post('', 'store')->middleware(['throttle:60,1']);
        Route::post('{order}/cancel', 'cancel');
    });
});
