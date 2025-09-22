<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ThirdParty\ThirdPartyAuthController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyController;
use App\Http\Controllers\API\ThirdParty\ThirdPartiesBankDetailsController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyCategoryController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyUserProfileController;
use App\Http\Controllers\Settings\Codes\ApiCurrencyController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;

// use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;
use App\Http\Controllers\Procurement\TenderApiController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationEvaluationController;
use App\Http\Controllers\Procurement\SupplierCategoryController;
use App\Http\Controllers\Procurement\SupplierCategoryApiController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\API\Enums\ThirdPartyTypesEnumController;

Route::prefix('third-party-auth')->group(function () {
    Route::post('login', [ThirdPartyAuthController::class, 'login']);
    Route::post('register', [ThirdPartyAuthController::class, 'register']); // Step 1: User personal registration
    Route::get('/email/verify/{id}/{hash}', [ThirdPartyAuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/resend-verification', [ThirdPartyAuthController::class, 'resendVerification'])->name('verification.resend')->middleware('throttle:6,1');
});

// step 2: Register company info (associated third party)
Route::post('third-parties/register-details', [ThirdPartyController::class, 'store']);

// Tenders
Route::get('/tenders', [TenderApiController::class, 'index']);
Route::post('/tenders', [TenderApiController::class, 'store']);
Route::put('/tenders/{id}', [TenderApiController::class, 'update']);
Route::delete('/tenders/{id}', [TenderApiController::class, 'destroy']);
Route::post('/tenders/{tenderId}/items', [TenderApiController::class, 'addItem']);
Route::delete('/tenders/{tenderId}/items/{itemId}', [TenderApiController::class, 'deleteItem']);
Route::post('/tenders/{tenderId}/suppliers', [TenderApiController::class, 'addSupplier']);
Route::delete('/tenders/{tenderId}/suppliers/{supplierId}', [TenderApiController::class, 'deleteSupplier']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/thirdpartyuser', function (Request $request) {
        return $request->user();
    })->name('thirdpartyuser.profile');

    Route::prefix('third-party-auth')->group(function () {
        Route::post('logout', [ThirdPartyAuthController::class, 'logout'])->name('third-party-auth.logout');
    });

    Route::prefix('third-party-profile')->group(function () {
        Route::get('/', [ThirdPartyUserProfileController::class, 'show']);
        Route::put('/', [ThirdPartyUserProfileController::class, 'update']);
        Route::patch('/', [ThirdPartyUserProfileController::class, 'partialUpdate']);
        Route::delete('/', [ThirdPartyUserProfileController::class, 'destroy']);
        Route::put('/password', [ThirdPartyUserProfileController::class, 'changePassword']);
    });

    Route::prefix('third-parties')->group(function () {
        Route::get('/', [ThirdPartyController::class, 'index']);

        Route::get('me', [ThirdPartyController::class, 'showMyThirdPartyDetails']);

        Route::get('{third_party}', [ThirdPartyController::class, 'show']);
        Route::put('{third_party}', [ThirdPartyController::class, 'update']);
        Route::delete('{third_party}', [ThirdPartyController::class, 'destroy']);
        // Route::get('suppliers', [ThirdPartyController::class, 'getSuppliers']);
        // Route::patch('{third_party}/approve', [ThirdPartyController::class, 'approve']);
        // Route::patch('{third_party}/reject', [ThirdPartyController::class, 'reject']);
        // Route::patch('{third_party}/status', [ThirdPartyController::class, 'updateStatus']);
    });

    Route::apiResource('third-parties-bank-details', ThirdPartiesBankDetailsController::class);

    Route::middleware('thirdparty.approved')->group(function () {
        Route::apiResource('third-party-categories', ThirdPartyCategoryController::class);
    });
});

// currencies
Route::prefix('v1')->group(function () {
    Route::get('currencies', [ApiCurrencyController::class, 'list']);
});

// enums (public)
Route::prefix('enums')->group(function () {
    Route::get('third-party-types', [ThirdPartyTypesEnumController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('proc')->name('proc.api.')->group(function () {
        Route::apiResource('supplier-cat', SupplierCategoryController::class);
        Route::apiResource('supp', SupplierController::class);
    });
});

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
        Route::get('clients/{client}/tickets', [\App\Http\Controllers\API\Channel\TicketController::class, 'index']);
        Route::post('clients/{client}/tickets', [\App\Http\Controllers\API\Channel\TicketController::class, 'store']);
    });

    Route::prefix('pbx')->middleware([\App\Http\Middleware\CheckTokenAndAddToHeaderMiddleware::class, \App\Http\Middleware\PBXAuthMiddleware::class])->group(function () {
        Route::get('contacts', [\App\Http\Controllers\API\PBX\ContactController::class, 'index']);
        Route::post('contacts/create', [\App\Http\Controllers\API\PBX\ContactController::class, 'store']);
        Route::post('calls', [\App\Http\Controllers\API\PBX\CallController::class, 'store']);
        Route::post('calls/missed', [\App\Http\Controllers\API\PBX\CallController::class, 'missed']);
        Route::post('calls/create', [\App\Http\Controllers\API\PBX\CallController::class, 'outgoing']);
        Route::post('calls/non-answer', [\App\Http\Controllers\API\PBX\CallController::class, 'noAnswer']);
    });

    Route::prefix('inventory')->group(function () {
        Route::get('item-categories', [\App\Http\Controllers\API\ItemCategories\ItemCategoriesController::class, 'index']);
    });
});


// API Routes (for Supplier Portal)
Route::prefix('procurement')->name('api.procurement.')
    ->middleware(['auth:sanctum'])
    ->group(function () {
        Route::apiResource('supplier-cat', SupplierCategoryApiController::class);
        Route::apiResource('supp', SupplierController::class);

        // Prequalification API endpoints
        Route::prefix('prequalification')->name('prequalification.')->group(function () {
            Route::get('rounds', [PrequalificationApplicationController::class, 'apiIndex'])->name('rounds.index');
            Route::get('rounds/{round}', [PrequalificationApplicationController::class, 'apiShow'])->name('rounds.show');
            Route::post('applications', [PrequalificationApplicationController::class, 'store'])->name('applications.store');
        });
    });

// Public (unauthenticated) read-only endpoints for prequalification rounds to support frontend path /api/prequalification/rounds
Route::prefix('prequalification')->name('api.prequalification.')->group(function () {
    Route::get('rounds', [PrequalificationApplicationController::class, 'apiIndex'])->name('rounds.index');
    Route::get('rounds/{round}', [PrequalificationApplicationController::class, 'apiShow'])->name('rounds.show');
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Admin and Public Routes for Prequalification Periods
    // Route::controller(PrequalificationPeriodController::class)->group(function () {
    //     Route::post('prequal-periods', 'store')->middleware('can:create,App\Models\Procurement\PrequalificationPeriod');
    //     Route::get('prequal-periods/{period}', 'show')->middleware('can:view,period');
    // });

    // Supplier Routes
    // Route::middleware('role:supplier')->group(function () {
    //     Route::controller(SupplierApplicationController::class)->group(function () {
    //         Route::post('supplier/applications', 'store');
    //         Route::get('supplier/applications/{application}', 'show')->middleware('can:view,application');
    //     });
    // });

    // Evaluator Routes
    Route::middleware('role:evaluator')->group(function () {
        Route::controller(PrequalificationEvaluationController::class)->group(function () {
            Route::post('evaluator/evaluations', 'store');
        });
    });
});
