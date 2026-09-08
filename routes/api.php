<?php

use App\Http\Controllers\Api\CurrentUserController;
use App\Http\Controllers\Api\HealthController;
use App\Http\Controllers\Api\IndexInvestmentController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LogoutController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ShowInvestmentController;
use App\Http\Controllers\Api\StoreInvestmentController;
use App\Http\Controllers\Api\WithdrawInvestmentController;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::post('/login', LoginController::class)->middleware('throttle:login');
Route::post('/register', RegisterController::class)->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/user', CurrentUserController::class);
    Route::post('/logout', LogoutController::class);

    Route::middleware('throttle:investments')->group(function (): void {
        Route::get('/investments', IndexInvestmentController::class);
        Route::post('/investments', StoreInvestmentController::class);
        Route::get('/investments/{investment}', ShowInvestmentController::class);
        Route::post('/investments/{investment}/withdraw', WithdrawInvestmentController::class);
    });
});
