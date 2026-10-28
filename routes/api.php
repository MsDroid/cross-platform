<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\{RegisterController, LoginController, LogoutController};
use App\Http\Controllers\Api\V1\Profile\{ProfileController, DeviceController};

Route::prefix('v1')->group(function () {
    Route::post('auth/register', RegisterController::class)->middleware('throttle:10,1');
    Route::post('auth/login', LoginController::class)->middleware('throttle:10,1');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', LogoutController::class);

        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'updatePassword']);
        Route::delete('profile', [ProfileController::class, 'destroy']);

        Route::post('devices', [DeviceController::class, 'store']);
        Route::delete('devices', [DeviceController::class, 'destroy']);
    });
});
