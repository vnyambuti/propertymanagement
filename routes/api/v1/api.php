<?php

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\Lease\LeaseController;
use App\Http\Controllers\Api\v1\Payment\PaymentController;
use App\Http\Controllers\Api\v1\Property\IndexController;
use App\Http\Controllers\Api\v1\Tenant\TenantController;
use App\Http\Controllers\Api\v1\Unit\UnitController;
use App\Http\Controllers\Api\v1\User\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



Route::prefix('auth')->group(function() {
    Route::post('/register', [AuthController::class, 'register']);
//improvement after profiling

        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);


    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});
Route::post('/{id}/send-receipt', [PaymentController::class, 'sendPaymentReceipt']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::resource('properties', IndexController::class);
     // User management routes
     Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/all', [UserController::class, 'all']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::post('/', [UserController::class, 'store']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::post('/{id}/change-password', [UserController::class, 'changePassword']);
        Route::get('/role/{role}', [UserController::class, 'getByRole']);
    })->middleware(['admin']);
    Route::prefix('payments')->group(function () {
        // Standard CRUD routes
        Route::get('/', [PaymentController::class, 'index']);
        Route::get('/{id}', [PaymentController::class, 'show']);
        Route::post('/', [PaymentController::class, 'store']);
        Route::put('/{id}', [PaymentController::class, 'update']);
        Route::delete('/{id}', [PaymentController::class, 'destroy']);

        // Additional payment-specific routes
        Route::get('/lease/{leaseId}', [PaymentController::class, 'getPaymentsByLease']);
        Route::post('/date-range', [PaymentController::class, 'getPaymentsByDateRange']);
        Route::get('/outstanding', [PaymentController::class, 'getOutstandingPayments']);
        Route::put('/{id}/complete', [PaymentController::class, 'markPaymentAsCompleted']);
        Route::post('/generate-invoice', [PaymentController::class, 'generateRentInvoice']);
    });
    Route::prefix('leases')->group(function () {
        // Basic CRUD operations
        Route::get('/', [LeaseController::class, 'index']);
        Route::post('/', [LeaseController::class, 'store']);
        Route::get('/{id}', [LeaseController::class, 'show']);
        Route::put('/{id}', [LeaseController::class, 'update']);
        Route::delete('/{id}', [LeaseController::class, 'destroy']);

        // Additional functionality routes
        Route::get('/unit/{unitId}', [LeaseController::class, 'getLeasesByUnit']);
        Route::get('/tenant/{tenantId}', [LeaseController::class, 'getLeasesByTenant']);
        Route::get('/active', [LeaseController::class, 'getActiveLeases']);
        Route::get('/expiring', [LeaseController::class, 'getExpiringLeases']);

        // Lease actions
        Route::post('/{id}/terminate', [LeaseController::class, 'terminateLease']);
        Route::post('/{id}/renew', [LeaseController::class, 'renewLease']);
    });

    Route::apiResource('units', UnitController::class);
    Route::prefix('units')->group(function () {
        Route::get('properties/{propertyId}/units', [UnitController::class, 'getUnitsByProperty']);
        Route::get('units/vacant', [UnitController::class, 'getVacantUnits']);
    });
    Route::apiResource('tenants', TenantController::class);

});


