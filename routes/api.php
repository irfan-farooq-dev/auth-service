<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/refresh', [AuthController::class, 'refresh']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt');

Route::get('/admin/dashboard', [AdminController::class, 'index'])
    ->middleware(['jwt', 'role:admin']);

Route::middleware('jwt')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
});

Route::get('/clear', function () {

    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    // Artisan::call('view:clear');

    return "Cleared!";
});
