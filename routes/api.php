<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\AboutController;
use App\Http\Controllers\Api\V1\GalleryController;

Route::post('/login', [AuthController::class, 'login']);

// Public read-only endpoints for website visitors
Route::prefix('v1')->group(function () {
    Route::get('galleries', [GalleryController::class, 'index']);
    Route::get('galleries/{gallery}', [GalleryController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::prefix('v1')->group(function () {
        Route::apiResource('banners', BannerController::class);
        Route::apiResource('services', ServiceController::class);
        Route::apiResource('abouts', AboutController::class);
        Route::apiResource('galleries', GalleryController::class)->except(['index', 'show']);
    });
});
