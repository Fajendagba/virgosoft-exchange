<?php

use App\Http\Controllers\API\v1\Auth\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AUTH API Routes - v1
|--------------------------------------------------------------------------
*/

Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('register', 'register')->middleware(['throttle:10,1']);
    Route::post('login', 'login')->middleware(['throttle:10,1']);

    Route::middleware(['auth:api'])->group(function () {
        Route::post('logout', 'logout');
    });
});
