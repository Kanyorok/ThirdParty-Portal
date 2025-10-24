<?php

use App\Http\Controllers\DMS\API\DocumentPreviewController;
use App\Http\Controllers\DMS\API\DocumentVerificationController;
use Illuminate\Support\Facades\Route;

/** ==================================================================================================================== */

Route::prefix('dms')->middleware([\App\Http\Middleware\DMSAuthMiddleware::class])->namespace('DMS/API')->group(function () {
    Route::get('preview', [DocumentPreviewController::class, '__invoke']);

    //TODO for verification specify the verification type
    Route::post('verification/data', [DocumentVerificationController::class, 'store']);
});
