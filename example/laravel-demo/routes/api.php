<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\V1\WorkflowConfigController;
use App\Http\Controllers\API\V1\WorkflowController;
use App\Http\Controllers\API\V1\RuleController;
use App\Http\Controllers\API\V1\NotificationController;
use App\Http\Controllers\API\V1\BatchController;
use App\Http\Controllers\API\V1\MetricsController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {


    Route::get('/modules/{module}/config-check', [WorkflowConfigController::class, 'check']);

    Route::apiResource('workflows', WorkflowController::class);
    Route::post('/workflows/{id}/approve', [WorkflowController::class, 'approve']);
    Route::post('/workflows/{id}/reject', [WorkflowController::class, 'reject']);

    Route::apiResource('rules', RuleController::class);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);

    Route::get('batches', [BatchController::class, 'index']);
    Route::post('batches/run', [BatchController::class, 'run']);

    Route::get('metrics/overview', [MetricsController::class, 'overview']);
})->middleware('auth:sanctum');
