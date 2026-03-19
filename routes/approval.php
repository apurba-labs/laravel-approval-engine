<?php

use Illuminate\Support\Facades\Route;
use ApurbaLabs\ApprovalEngine\Http\Controllers\ApprovalController;

Route::get('/batch/{token}', [ApprovalController::class, 'approve'])
    ->name('approval.batch.approve');
