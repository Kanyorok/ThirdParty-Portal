<?php

use App\Http\Controllers\API\Channel\CodesController;
use App\Http\Controllers\API\Channel\LeadController;
use App\Http\Controllers\API\Channel\ReviewsController;
use App\Http\Controllers\API\Channel\SurveyController;
use App\Http\Controllers\API\Channel\TicketController;
use App\Http\Controllers\API\ItemCategories\ItemCategoriesController;
use App\Http\Controllers\API\PBX\CallController;
use App\Http\Controllers\API\PBX\ContactController;
use App\Http\Controllers\API\ThirdParty\ThirdPartiesBankDetailsController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyAuthController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyCategoryController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyUserProfileController;
use App\Http\Controllers\DMS\API\DocumentPreviewController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationEvaluationController;
use App\Http\Controllers\Procurement\SupplierCategoryApiController;
use App\Http\Controllers\Procurement\SupplierCategoryController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\TenderApiController;
use App\Http\Controllers\Settings\Codes\ApiCurrencyController;
use App\Http\Middleware\ChannelAuthMiddleware;
use App\Http\Middleware\CheckTokenAndAddToHeaderMiddleware;
use App\Http\Middleware\DocuwareAuthMiddleware;
use App\Http\Middleware\PBXAuthMiddleware;
use App\Http\Middleware\WebsiteAuthMiddleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('proc')->name('proc.api.')->group(function () {
        Route::apiResource('supplier-cat', SupplierCategoryController::class);
        Route::apiResource('supp', SupplierController::class);
    });
});

Route::prefix('v1')->group(function () {
    Route::prefix('website')->middleware(WebsiteAuthMiddleware::class)->group(function () {
        Route::post('reviews', \App\Http\Controllers\API\Website\ReviewsController::class);
        Route::get('survey', [\App\Http\Controllers\API\Website\SurveyController::class, 'index']);
        Route::post('survey', [\App\Http\Controllers\API\Website\SurveyController::class, 'store']);
    });

    Route::prefix('channels')->middleware(ChannelAuthMiddleware::class)->group(function () {
        Route::post('reviews', ReviewsController::class);
        Route::get('survey', [SurveyController::class, 'index']);
        Route::post('survey', [SurveyController::class, 'store']);
        Route::get('codes', CodesController::class);
        Route::post('lead/company', [LeadController::class, 'company']);
        Route::post('lead/individual', [LeadController::class, 'individual']);
        Route::get('clients/{client}/tickets', [TicketController::class, 'index']);
        Route::post('clients/{client}/tickets', [TicketController::class, 'store']);
    });

    Route::prefix('pbx')->middleware([CheckTokenAndAddToHeaderMiddleware::class, PBXAuthMiddleware::class])->group(function () {
        Route::get('contacts', [ContactController::class, 'index']);
        Route::post('contacts/create', [ContactController::class, 'store']);
        Route::post('calls', [CallController::class, 'store']);
        Route::post('calls/missed', [CallController::class, 'missed']);
        Route::post('calls/create', [CallController::class, 'outgoing']);
        Route::post('calls/non-answer', [CallController::class, 'noAnswer']);
    });

    Route::prefix('dms')->middleware([DocuwareAuthMiddleware::class])->namespace('DMS/API')->group(function () {
        Route::get('preview', [DocumentPreviewController::class, '__invoke']);

    });

    Route::prefix('inventory')->group(function () {
        Route::get('item-categories', [ItemCategoriesController::class, 'index']);
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
