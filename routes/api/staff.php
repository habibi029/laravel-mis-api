<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\StaffController;
use App\Http\Controllers\API\StaffDashboardController;
use App\Http\Controllers\ProductTransactionController;
use App\Http\Controllers\API\ExerciseTransactionController;

Route::prefix('staff')->middleware(['auth:api-staff','scopes:staff_user'])->group(function(){
    Route::get('/auth',[AuthController::class, 'auth']);
    Route::post('/logout',[AuthController::class, 'logout']);

    // --- client
    Route::get('/show-client', [StaffController::class, 'show_clients']);
    Route::post('/store-client', [StaffController::class, 'store_clients']);
    Route::get('/edit-client/{id}', [StaffController::class, 'edit_clients']);
    Route::post('/update-client/{id}', [StaffController::class, 'update_clients']);

    // --- dropdown
    Route::get('/show-client-list', [StaffController::class, 'show_all_clients']);
    Route::get('/show-staff-list', [StaffController::class, 'show_all_staffs']);
    Route::get('/show-exercise-list', [StaffController::class, 'show_all_exercises']);

    // --- product
    Route::post('/cart/checkout', [ProductTransactionController::class, 'checkout']);
    Route::get('/cart/show', [ProductTransactionController::class, 'show']);

    // --- exercise
    Route::post('/exercise-transaction/add', [ExerciseTransactionController::class, 'store']);
    Route::get('/exercise-transaction/show', [ExerciseTransactionController::class, 'show']);

    // --- inventory
    Route::get('/inventory-lists', [StaffController::class, 'show_inventories']);

    // --- attendance
    Route::get('/attendance-lists', [StaffController::class, 'show_staff_attendances']);

    // --- dashboard
    Route::get('/dashboard', [StaffDashboardController::class, 'index']);
    Route::get('/show-attendance-list/{id?}', [StaffController::class, 'show_staff_attendances']);
    Route::get('/show-staff', [StaffController::class, 'show_staffs']);

});
