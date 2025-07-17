<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ThirdParty\ThirdPartyAuthController;
use App\Http\Controllers\ThirdParty\ThirdPartyController;
use App\Http\Controllers\ThirdParty\ThirdPartiesBankDetailsController;
use App\Http\Controllers\ThirdParty\ThirdPartyCategoryController;
use App\Http\Controllers\ThirdParty\ThirdPartyUserProfileController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('third-party-auth')->group(function () {
    Route::post('login', [ThirdPartyAuthController::class, 'login']);
    Route::post('register', [ThirdPartyAuthController::class, 'register']);
    Route::middleware('auth:sanctum')->post('logout', [ThirdPartyAuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum'])->prefix('third-party-profile')->group(function () {
    Route::get('/', [ThirdPartyUserProfileController::class, 'show']);
    Route::put('/', [ThirdPartyUserProfileController::class, 'update']);
    Route::delete('/', [ThirdPartyUserProfileController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'thirdparty.approved'])->prefix('third-parties')->group(function () {
    Route::get('/', [ThirdPartyController::class, 'index']);
    Route::post('/', [ThirdPartyController::class, 'store']);
    Route::get('{third_party}', [ThirdPartyController::class, 'show']);
    Route::put('{third_party}', [ThirdPartyController::class, 'update']);
    Route::delete('{third_party}', [ThirdPartyController::class, 'destroy']);
    Route::get('suppliers', [ThirdPartyController::class, 'getSuppliers']);
    Route::post('{third_party}/approve', [ThirdPartyController::class, 'approve']);
    Route::post('{third_party}/reject', [ThirdPartyController::class, 'reject']);
    Route::patch('{third_party}/status', [ThirdPartyController::class, 'updateStatus']);
});

Route::middleware('auth:sanctum', 'thirdparty.approved')->apiResource('third-parties-bank-details', ThirdPartiesBankDetailsController::class);

Route::middleware('auth:sanctum', 'thirdparty.approved')->apiResource('third-party-categories', ThirdPartyCategoryController::class);

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

    Route::prefix('inventory')->group(function () {
        Route::get('item-categories', [\App\Http\Controllers\API\ItemCategories\ItemCategoriesController::class, 'index']);
    });
});
