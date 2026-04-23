<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\Admin\VehicleController as AdminVehicleController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\RentalController;
use App\Http\Controllers\Api\PaymentController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Vehicles
    Route::get('/vehicles', [VehicleController::class, 'index']);
    Route::get('/vehicles/available', [VehicleController::class, 'available']);
    Route::get('/vehicles/{id}', [VehicleController::class, 'show']);

    // Reservations
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel']);

    // Rentals
    Route::post('/rentals/checkout', [RentalController::class, 'checkout']);
    Route::post('/rentals/{rental}/checkin', [RentalController::class, 'checkin']);
    Route::get('/rentals', [RentalController::class, 'index']);

    // Payments
    Route::post('/rentals/{rental}/pay', [PaymentController::class, 'pay']);

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::apiResource('vehicles', AdminVehicleController::class);
    });
});
