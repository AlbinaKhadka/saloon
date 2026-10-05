<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\BannerController;
use App\Http\Controllers\Api\V1\ServiceCategoryController;
use App\Http\Controllers\Api\V1\ServiceController;
use App\Http\Controllers\Api\V1\AboutController;
use App\Http\Controllers\Api\V1\GalleryController;

use Illuminate\Support\Facades\Artisan;

Route::post('/login', [AuthController::class, 'login']);

Route::get('/run-migration', function () {
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('storage:link', ['--force' => true]);
    Artisan::call('config:clear');

    return response()->json([
        'message' => 'Migrations and storage link executed successfully!',
        'output' => Artisan::output(),
    ]);
});

// Public read-only endpoints for website visitors
Route::prefix('v1')->group(function () {
    Route::get('run-migration', function () {
        Artisan::call('migrate', ['--force' => true]);
        Artisan::call('storage:link', ['--force' => true]);
        Artisan::call('config:clear');

        return response()->json([
            'message' => 'Migrations and storage link executed successfully!',
            'output' => Artisan::output(),
        ]);
    });
    Route::get('banners', [BannerController::class, 'index']);
    Route::get('banners/{banner}', [BannerController::class, 'show']);
    Route::get('service-categories', [ServiceCategoryController::class, 'index']);
    Route::get('service-categories/{service_category}', [ServiceCategoryController::class, 'show']);
    Route::get('services', [ServiceController::class, 'index']);
    Route::get('services/{service}', [ServiceController::class, 'show']);
    Route::get('galleries', [GalleryController::class, 'index']);
    Route::get('galleries/{gallery}', [GalleryController::class, 'show']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::prefix('v1')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('change-password', [AuthController::class, 'changePassword']);
        Route::apiResource('banners', BannerController::class)->except(['index', 'show']);
        Route::apiResource('service-categories', ServiceCategoryController::class)->except(['index', 'show']);
        Route::apiResource('services', ServiceController::class)->except(['index', 'show']);
        Route::apiResource('abouts', AboutController::class);
        Route::apiResource('galleries', GalleryController::class)->except(['index', 'show']);
    });
});
