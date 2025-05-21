<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminController;
use App\Http\Controllers\API\StaffController;

Route::group(['middleware' => ['guest.api']], function () {
    Route::middleware(['throttle:login'])->post('/login',[AuthController::class, 'login']);
    Route::post('/reset-password/answers', [AuthController::class, 'question_and_answer']);
});

