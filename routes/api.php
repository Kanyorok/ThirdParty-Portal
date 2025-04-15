<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('website')->middleware(\App\Http\Middleware\WebsiteAuthMiddleware::class)->group(function () {
        Route::post('reviews', \App\Http\Controllers\API\Website\ReviewsController::class);
        Route::get('survey', [\App\Http\Controllers\API\Website\SurveyController::class, 'index']);
        Route::post('survey', [\App\Http\Controllers\API\Website\SurveyController::class, 'store']);
    });

    Route::prefix('channels')->middleware(\App\Http\Middleware\ChannelAuthMiddleware::class)->group(function () {
        Route::post('reviews', \App\Http\Controllers\API\Channel\ReviewsController::class);
        Route::get('survey', [\App\Http\Controllers\API\Channel\SurveyController::class, 'index']);
        Route::post('survey', [\App\Http\Controllers\API\Channel\SurveyController::class, 'store']);

        Route::get('codes', \App\Http\Controllers\API\Channel\CodesController::class);

        Route::post('lead/company', [\App\Http\Controllers\API\Channel\LeadController::class, 'company']);
        Route::post('lead/individual', [\App\Http\Controllers\API\Channel\LeadController::class, 'individual']);

        // Route::get('tickets/categories', \App\Http\Controllers\API\Channel\TicketCategoryController::class);
        Route::get('clients/{client}/tickets', [\App\Http\Controllers\API\Channel\TicketController::class, 'index']);
        Route::post('clients/{client}/tickets', [\App\Http\Controllers\API\Channel\TicketController::class, 'store']);
    });

    Route::prefix('pbx')->middleware([\App\Http\Middleware\CheckTokenAndAddToHeaderMiddleware::class, \App\Http\Middleware\PBXAuthMiddleware::class])->group(function () {
        Route::get('contacts', [\App\Http\Controllers\API\PBX\ContactController::class, 'index']);
        Route::post('contacts/create', [\App\Http\Controllers\API\PBX\ContactController::class, 'store']);

        // Route::post('create', \App\Http\Controllers\API\PBX\LeadController::class);

        Route::post('calls', [\App\Http\Controllers\API\PBX\CallController::class, 'store']);
        Route::post('calls/missed', [\App\Http\Controllers\API\PBX\CallController::class, 'missed']);
        Route::post('calls/create', [\App\Http\Controllers\API\PBX\CallController::class, 'outgoing']);
        Route::post('calls/non-answer', [\App\Http\Controllers\API\PBX\CallController::class, 'noAnswer']);
    });
});
