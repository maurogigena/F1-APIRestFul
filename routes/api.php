<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CircuitController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// http://localhost:8000/api/

Route::post('/login', [AuthController::class, 'login']);

Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->group(function () {

    // Users
    Route::apiResource('users', UserController::class)->except(['update', 'replace']);
    Route::patch('users/{user}', [UserController::class, 'update']);
    Route::put('users/{user}', [UserController::class, 'replace']);

    // Drivers
    Route::apiResource('drivers', DriverController::class)->except(['update', 'replace']);
    Route::patch('drivers/{driver}', [DriverController::class, 'update']);
    Route::put('drivers/{driver}', [DriverController::class, 'replace']);

    // Circuits    
    Route::apiResource('circuits', CircuitController::class)->except(['update', 'replace']);
    Route::patch('circuits/{circuit}', [CircuitController::class, 'update']);
    Route::put('circuits/{circuit}', [CircuitController::class, 'replace']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});
