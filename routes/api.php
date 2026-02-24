<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // Role & Permission Management
    Route::apiResource('roles', RoleController::class)->middleware('role:admin');
    Route::apiResource('permissions', PermissionController::class)->middleware('role:admin');

    // Event APIs
    Route::get('/events', [EventController::class, 'index']); // Public-ish (any authenticated user)
    Route::get('/events/{id}', [EventController::class, 'show']);
    Route::post('/events', [EventController::class, 'store'])->middleware('role:organizer|admin');
    Route::put('/events/{id}', [EventController::class, 'update'])->middleware('role:organizer|admin');
    Route::delete('/events/{id}', [EventController::class, 'destroy'])->middleware('role:organizer|admin');

    // Ticket APIs
    Route::post('/events/{event_id}/tickets', [TicketController::class, 'store'])->middleware('role:organizer|admin');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->middleware('role:organizer|admin');
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy'])->middleware('role:organizer|admin');

    // Booking APIs
    Route::post('/tickets/{id}/bookings', [BookingController::class, 'store'])->middleware(['role:customer|admin', 'prevent_double_booking']);
    Route::get('/bookings', [BookingController::class, 'index'])->middleware('role:customer|admin');
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancel'])->middleware('role:customer|admin');

    // Payment APIs
    Route::post('/bookings/{id}/payment', [PaymentController::class, 'store']); // Mock payment
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
});
