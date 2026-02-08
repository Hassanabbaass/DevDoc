<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TeamController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public Authentication Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1'); // Rate limit: 5 attempts per minute

// Protected Routes (Require Authentication)
Route::middleware('auth:api')->group(function () {
    // User Profile
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // Team Management
    Route::post('/teams', [TeamController::class, 'store']);
    Route::post('/teams/join', [TeamController::class, 'join']);
});