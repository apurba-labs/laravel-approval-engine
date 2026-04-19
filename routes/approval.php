<?php

use Illuminate\Support\Facades\Route;
use ApurbaLabs\ApprovalEngine\Http\Controllers\Api\ApprovalTokenController;

Route::prefix('api/v1')->group(function () {

    Route::post('/approvals/token/approve', [
        ApprovalTokenController::class,
        'approve'
    ])->name('approval.token.approve');

});