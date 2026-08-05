<?php

use App\Http\Controllers\Api\AppointmentController as ApiAppointmentController;
use App\Http\Controllers\Api\AppointmentSlotController;
use App\Http\Controllers\Api\DepartmentController as ApiDepartmentController;
use App\Http\Controllers\Api\FollowUpController as ApiFollowUpController;
use App\Http\Controllers\Api\PaymentController as ApiPaymentController;
use App\Http\Controllers\ContextSwitcherController;
use App\Http\Controllers\InsuranceController;
use App\Support\Permissions;
use Illuminate\Support\Facades\Route;

Route::get('/appointments/available-slots', [AppointmentSlotController::class, 'index'])
    ->middleware('throttle:30,1')
    ->name('api.appointments.available-slots');

Route::get('/insurances', [InsuranceController::class, 'publicList'])
    ->middleware('throttle:30,1')
    ->name('api.insurances.list');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/context-switching', [ContextSwitcherController::class, 'getOptions'])
        ->name('api.context-switching');

    Route::prefix('v1')->group(function () {
        Route::middleware('role_or_permission:'.Permissions::staffOrPatient(Permissions::MANAGE_APPOINTMENTS))->group(function () {
            Route::get('/appointments', [ApiAppointmentController::class, 'index'])->name('api.appointments.index');
        });

        Route::middleware('permission:'.Permissions::VIEW_DEPARTMENTS)->group(function () {
            Route::get('/departments', [ApiDepartmentController::class, 'index'])->name('api.departments.index');
            Route::get('/departments/{id}', [ApiDepartmentController::class, 'show'])->name('api.departments.show');
        });

        Route::middleware('permission:'.Permissions::MANAGE_PAYMENTS)->group(function () {
            Route::get('/payments', [ApiPaymentController::class, 'index'])->name('api.payments.index');
            Route::get('/payments/{id}', [ApiPaymentController::class, 'show'])->name('api.payments.show');
        });

        Route::middleware('permission:'.Permissions::MANAGE_FOLLOW_UPS)->group(function () {
            Route::get('/follow-ups', [ApiFollowUpController::class, 'index'])->name('api.follow-ups.index');
            Route::get('/follow-ups/upcoming', [ApiFollowUpController::class, 'upcoming'])->name('api.follow-ups.upcoming');
            Route::get('/follow-ups/{id}', [ApiFollowUpController::class, 'show'])->name('api.follow-ups.show');
        });
    });
});
