<?php

use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\JudgeController;
use App\Http\Controllers\SchedulingController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::post('/register', [AuthController::class, 'register'])->middleware('role:admin');

    Route::post('/cases/classify/preview', [CaseController::class, 'preview'])->middleware('role:admin,clerk');
    Route::get('/cases', [CaseController::class, 'index'])->middleware('role:admin,clerk,judge');
    Route::post('/cases', [CaseController::class, 'store'])->middleware('role:admin,clerk');
    Route::get('/cases/{id}', [CaseController::class, 'show'])->middleware('role:admin,clerk,judge');
    Route::put('/cases/{id}', [CaseController::class, 'update'])->middleware('role:admin,judge');
    Route::delete('/cases/{id}', [CaseController::class, 'destroy'])->middleware('role:admin');

    Route::post('/schedule/auto', [SchedulingController::class, 'auto'])->middleware('role:admin');
    Route::post('/schedule/manual', [SchedulingController::class, 'manual'])->middleware('role:admin');
    Route::get('/schedule/events', [SchedulingController::class, 'events'])->middleware('role:admin,judge');

    Route::get('/judges', [JudgeController::class, 'index'])->middleware('role:admin,clerk,judge');
    Route::get('/judges/{id}', [JudgeController::class, 'show'])->middleware('role:admin,clerk,judge');
    Route::put('/judges/{id}/availability', [JudgeController::class, 'upsertAvailability'])->middleware('role:admin');

    Route::get('/analytics/overview', [AnalyticsController::class, 'overview'])->middleware('role:admin,clerk,judge');
    Route::get('/analytics/backlog', [AnalyticsController::class, 'backlog'])->middleware('role:admin,clerk,judge');
    Route::get('/analytics/workload', [AnalyticsController::class, 'workload'])->middleware('role:admin,clerk,judge');
});
