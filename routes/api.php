<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\LoginController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('v1')->group(function () {
    Route::post('/auth/login', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'login'])->middleware('throttle:5,1');
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/admin/logout', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'logout']);
        Route::get('/admin/me', [\App\Http\Controllers\Api\V1\Auth\LoginController::class, 'me']);
        
        Route::apiResource('banners', \App\Http\Controllers\Api\V1\BannerController::class);
    });
});
