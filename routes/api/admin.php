<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\AdminDashboardController;
use App\Http\Controllers\ProductTransactionController;
use App\Http\Controllers\API\ExerciseTransactionController;

Route::prefix('admin')->middleware(['auth:api-admin', 'scopes:admin_user'])->group(function () {
    Route::get('/auth', [AuthController::class, 'auth']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- client
    Route::get('/show-client', [AdminController::class, 'show_clients']);
    Route::post('/store-client', [AdminController::class, 'store_clients']);
    Route::get('/edit-client/{id}', [AdminController::class, 'edit_clients']);
    Route::post('/update-client/{id}', [AdminController::class, 'update_clients']);

    Route::post('/soft-delete-client/{id}', [AdminController::class, 'soft_delete_clients']);
    Route::get('/archive-client', [AdminController::class, 'trashed_record_clients']);
    Route::post('/force-delete-client/{id}', [AdminController::class, 'force_delete_clients']);
    Route::post('/restore-client/{id}', [AdminController::class, 'restore_clients']);

    // --- staff
    Route::get('/show-staff', [AdminController::class, 'show_staffs']);
    Route::post('/store-staff', [AdminController::class, 'store_staffs']);
    Route::get('/edit-staff/{id}', [AdminController::class, 'edit_staffs']);
    Route::post('/update-staff/{id}', [AdminController::class, 'update_staffs']);

    Route::post('/soft-delete-staff/{id}', [AdminController::class, 'soft_delete_staffs']);       // soft delete
    Route::get('/archive-staff', [AdminController::class, 'trashed_record_staffs']);              // used to display archived records
    Route::post('/force-delete-staff/{id}', [AdminController::class, 'force_delete_staffs']);     // used to permanently delete records in archive
    Route::post('/restore-staff/{id}', [AdminController::class, 'restore_staffs']);               // used to restore deleted records in archive

    // --- exercise
    Route::get('/show-exercise', [AdminController::class, 'show_exercises']);
    Route::post('/store-exercise', [AdminController::class, 'store_exercises']);
    Route::get('/edit-exercise/{id}', [AdminController::class, 'edit_exercises']);
    Route::post('/update-exercise/{id}', [AdminController::class, 'update_exercises']);

    Route::post('/soft-delete-exercise/{id}', [AdminController::class, 'soft_delete_exercises']);       // soft delete
    Route::get('/archive-exercise', [AdminController::class, 'trashed_record_exercise']);              // used to display archived records
    Route::post('/force-delete-exercise/{id}', [AdminController::class, 'hard_delete_exercises']);     // used to permanently delete records in archive
    Route::post('/restore-exercise/{id}', [AdminController::class, 'restore_exercises']);

    // --- position
    Route::get('/show-position', [AdminController::class, 'show_positions']);
    Route::post('/store-position', [AdminController::class, 'store_positions']);
    Route::get('/edit-position/{id}', [AdminController::class, 'edit_positions']);
    Route::post('/update-position/{id}', [AdminController::class, 'update_positions']);

    Route::post('/soft-delete-position/{id}', [AdminController::class, 'soft_delete_positions']);       // soft delete
    Route::get('/archive-position', [AdminController::class, 'trashed_record_positions']);              // used to display archived records
    Route::post('/force-delete-position/{id}', [AdminController::class, 'hard_delete_positions']);     // used to permanently delete records in archive
    Route::post('/restore-position/{id}', [AdminController::class, 'restore_positions']);

    // --- inventory
    Route::get('/show-inventory', [AdminController::class, 'show_inventories']);
    Route::post('/store-inventory', [AdminController::class, 'store_inventories']);
    Route::get('/edit-inventory/{id}', [AdminController::class, 'edit_inventories']);
    Route::post('/update-inventory/{id}', [AdminController::class, 'update_inventories']);

    Route::post('/soft-delete-inventory/{id}', [AdminController::class, 'soft_delete_inventories']);       // soft delete
    Route::get('/archive-inventory', [AdminController::class, 'trashed_record_inventories']);              // used to display archived records
    Route::post('/force-delete-inventory/{id}', [AdminController::class, 'hard_delete_inventories']);     // used to permanently delete records in archive
    Route::post('/restore-inventory/{id}', [AdminController::class, 'restore_inventories']);

    // --- attendance
    Route::get('/show-attendance-list/{id?}', [AdminController::class, 'show_staff_attendances']);
    Route::post('/store-attendance/{id}', [AdminController::class, 'store_staff_attendances']);
    Route::get('/edit-attendance/{id}', [AdminController::class, 'edit_staff_attendances']);
    Route::post('/update-attendance/{id}', [AdminController::class, 'update_staff_attendances']);

    // --- clock in and out
    Route::post('/clockIn', [AdminController::class, 'employee_clockIn']);
    // --- mark absent
    Route::post('/mark-absent', [AdminController::class, 'markAbsent']);
    Route::post('/mark-leave', [AdminController::class, 'markLeave']);

    Route::post('/soft-delete-attendance/{id}', [AdminController::class, 'soft_delete_staff_attendances']);       // soft delete
    Route::get('/archive-attendance', [AdminController::class, 'trashed_record_attendancess']);              // used to display archived records
    Route::post('/force-delete-attendance/{id}', [AdminController::class, 'hard_delete_staff_attendances']);     // used to permanently delete records in archive
    Route::post('/restore-attendance/{id}', [AdminController::class, 'restore_staff_attendances']);

    // --- payroll
    Route::get('/show-staff-payroll', [AdminController::class, 'show_staff_payrolls']);
    Route::post('/store-staff-payroll/{id}', [AdminController::class, 'store_staff_payrolls']);

    Route::post('/soft-delete-payroll/{id}', [AdminController::class, 'soft_delete_staff_payrolls']);       // soft delete
    Route::get('/archive-payroll', [AdminController::class, 'trashed_record_payrolls']);              // used to display archived records
    Route::post('/force-delete-payroll/{id}', [AdminController::class, 'hard_delete_staff_payrolls']);     // used to permanently delete records in archive
    Route::post('/restore-payroll/{id}', [AdminController::class, 'restore_staff_payrolls']);

    // --- pos exercise
    Route::get('/exercise-transaction/show', [ExerciseTransactionController::class, 'show']); // show archive
    Route::get('/exercise-transaction/archive', [ExerciseTransactionController::class, 'trashed_record_exercise_transaction']);
    Route::post('/exercise-transaction/delete/{transaction_code}', [ExerciseTransactionController::class, 'soft_delete_exercise_transaction']); // soft delete
    Route::post('/exercise-transaction/restore/{transaction_code}', [ExerciseTransactionController::class, 'restore_record_exercise_transaction']); // restore archive
    Route::post('/exercise-transaction/delete-permanent/{transaction_code}', [ExerciseTransactionController::class, 'force_delete_record_exercise_transaction']); // permanent delete

    // --- pos product
    Route::get('/cart/show', [ProductTransactionController::class, 'show']);
    Route::post('/cart/soft-delete/{id}', [ProductTransactionController::class, 'soft_delete_product_transaction']);
    Route::post('/cart/restore/{id}', [ProductTransactionController::class, 'restore_product_transaction']);
    Route::post('/cart/delete-permanent/{id}', [ProductTransactionController::class, 'force_delete_product_transaction']);
    Route::get('/cart/archive', [ProductTransactionController::class, 'trashed_record_exercise_transaction']);

    // --- backups
    Route::get('/database/backup', [AdminController::class, 'backup']);

    // --- dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index']);
});