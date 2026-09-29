<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\Api\AuthController;

Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'user']);

// Migration & Storage link helper endpoint for Render Free Tier
Route::get('/run-migration', function () {
    Artisan::call('migrate', ['--force' => true]);
    Artisan::call('storage:link', ['--force' => true]);
    Artisan::call('config:clear');

    return response()->json([
        'message' => 'Migrations and storage link executed successfully!',
        'output' => Artisan::output(),
    ]);
});
