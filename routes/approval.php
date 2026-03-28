<?php

use Illuminate\Support\Facades\Route;
use ApurbaLabs\ApprovalEngine\Http\Controllers\ApprovalController;

use ApurbaLabs\ApprovalEngine\Domains\WorkflowForm\Http\Controllers\Api\WorkflowFormController;

Route::prefix('api/v1')->group(function () {

    Route::get('/batch/{token}', [ApprovalController::class, 'approve'])
        ->name('approval.batch.approve');

    Route::prefix('workflow/forms')->group(function () {
        Route::get('{module}', [WorkflowFormController::class, 'show']);
        Route::post('{module}', [WorkflowFormController::class, 'submit']);
    });

});