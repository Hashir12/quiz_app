<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\CourseController;
use \App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\FeedQuestionController;
use App\Http\Controllers\FeedAnswerController;
Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('v1')->group(function () {
        Route::apiResource('course',CourseController::class);
        Route::apiResource('post',PostController::class);
        Route::apiResource('comment',CommentController::class);
        Route::apiResource('feed-question',FeedQuestionController::class);
        Route::apiResource('feed-answer',FeedAnswerController::class);
        Route::get('get-user-streak/{id}',[AuthController::class, 'getUserStreak']);
    });

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password-email', [AuthController::class, 'resetPasswordEmail']);
Route::post('/reset-password-otp', [AuthController::class, 'resetPasswordOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
