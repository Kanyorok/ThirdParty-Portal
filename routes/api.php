<?php

use App\Http\Controllers\API\Enums\ThirdPartyTypesEnumController;
use App\Http\Controllers\API\Procurement\SupplierRFQController;
use App\Http\Controllers\API\Procurement\TenderClarificationApiController;
use App\Http\Controllers\API\ThirdParty\ThirdPartiesBankDetailsController;
// use App\Http\Controllers\Procurement\ThirdParties\ThirdPartyAuthController;
use App\Http\Controllers\ThirdParty\ThirdPartyAuthController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyCategoryController;
// use App\Http\Controllers\API\ThirdParty\ThirdPartyController;
use App\Http\Controllers\Procurement\ThirdParties\ThirdPartiesController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyProfileController;
use App\Http\Controllers\Settings\Codes\ApiCurrencyController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController;
// use App\Http\Controllers\Procurement\Prequalification\PrequalificationEvaluationController;
use App\Http\Controllers\Procurement\SupplierCategoryController;
use App\Http\Controllers\Procurement\SupplierCategoryApiController;
use App\Http\Controllers\Procurement\SupplierController;
use App\Http\Controllers\Procurement\Prequalification\PrequalificationProgressController;
use App\Http\Controllers\Procurement\TenderApiController;
use App\Http\Controllers\Procurement\TenderInvitationController;
use App\Http\Controllers\API\DMS\DocumentApiController;
// use App\Http\Controllers\Procurement\TenderDocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ThirdParty\ThirdPartyDocumentsController;
// use App\Http\Controllers\Settings\WorkFlowController;

// Token validation (Sanctum) for frontend session checks
Route::post('auth/validate-token', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        return response()->json(['valid' => false], 401);
    }

    // ThirdPartyUser specific flags
    $isActive = method_exists($user, 'isActive') ? $user->isActive() : (bool)($user->IsActive ?? $user->isActive ?? false);
    $isApproved = method_exists($user, 'isApproved') ? $user->isApproved() : (bool)($user->isApproved ?? false);

    if (!$isActive || !$isApproved) {
        return response()->json(['valid' => false], 403);
    }

    return response()->json([
        'valid' => true,
        'user' => [
            'id' => $user->getAuthIdentifier(),
            'email' => $user->email ?? $user->Email ?? null,
            'isActive' => $isActive,
            'isApproved' => $isApproved,
        ],
    ]);
})->middleware('auth:sanctum')->name('auth.validate-token');

// Route::prefix('third-party-auth')->group(function () {
//     Route::post('login', [ThirdPartyAuthController::class, 'login']);
//     Route::post('register', [ThirdPartyAuthController::class, 'register']); // Step 1: User personal registration
//     Route::get('/email/verify/{id}/{hash}', [ThirdPartyAuthController::class, 'verifyEmail'])->name('verification.verify');
//     Route::post('/email/resend-verification', [ThirdPartyAuthController::class, 'resendVerification'])->name('verification.resend')->middleware('throttle:6,1');
// });

// step 2: Register company info (associated third party)
Route::post('third-parties/register-details', [ThirdPartiesController::class, 'store']);

// Health check endpoint
Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now(),
        'service' => 'BRERP API'
    ]);
});

// Test endpoint for debugging (NO AUTH REQUIRED)
Route::get('/debug/tender-invitations', function (Illuminate\Http\Request $request) {
    try {
        $thirdPartyId = $request->query('third_party_id', 1); // Default to ID 1 for testing

        // Get supplier ID from third party ID
        $supplier = \App\Models\ThirdParies\Supplier::whereHas('thirdParty', function ($query) use ($thirdPartyId) {
            $query->where('Id', $thirdPartyId);
        })->first();

        if (!$supplier) {
            return response()->json([
                'debug' => 'No supplier found',
                'third_party_id' => $thirdPartyId,
                'third_parties_count' => DB::table('t_ThirdParties')->count(),
                'suppliers_count' => DB::table('t_Suppliers')->count(),
                'sample_third_party' => DB::table('t_ThirdParties')->first()
            ]);
        }

        // Fetch tender invitations
        $invitations = \App\Models\Procurement\TenderInvitation::where('SupplierId', $supplier->Id)
            ->with(['tender'])
            ->take(5)
            ->get();

        return response()->json([
            'debug' => 'Debug endpoint working',
            'third_party_id' => $thirdPartyId,
            'supplier_found' => $supplier ? $supplier->Id : null,
            'invitations_count' => $invitations->count(),
            'invitations' => $invitations->map(function ($inv) {
                return [
                    'InvitationID' => $inv->InvitationID,
                    'TenderId' => (int)$inv->TenderId,
                    'ResponseStatus' => strtolower($inv->ResponseStatus),
                    'tender_title' => $inv->tender ? $inv->tender->Title : 'No tender loaded'
                ];
            })
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'debug' => 'Error in debug endpoint',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
});


// Tenders API (public index to allow portal to call with third_party_id)
Route::apiResource('tenders', TenderApiController::class);
Route::get('/tender-invitations', [TenderInvitationController::class, 'index']);
Route::put('/tender-invitations/{id}', [TenderInvitationController::class, 'update']);

// DMS: Documents visible to authenticated user
Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifiedUser::class])->group(function () {
    Route::get('/dms/documents', [DocumentApiController::class, 'index']);
});

// Keep DMS central; tender documents are managed via DMS and relations

// Tender Clarification APIs
Route::post('/tender-clarifications', [TenderClarificationApiController::class, 'submitClarification']);
Route::get('/tender-clarifications', [TenderClarificationApiController::class, 'getClarifications']);
Route::get('/tender-clarifications/pending', [TenderClarificationApiController::class, 'getPendingClarifications']);
Route::put('/tender-clarifications/{id}/respond', [TenderClarificationApiController::class, 'respondToClarification']);

// Bid Submission APIs
Route::post('/bid-submissions', [\App\Http\Controllers\API\Procurement\BidSubmissionApiController::class, 'store']);
Route::get('/bid-submissions', [\App\Http\Controllers\API\Procurement\BidSubmissionApiController::class, 'getSupplierBids']);
Route::get('/bid-submissions/existing', [\App\Http\Controllers\API\Procurement\BidSubmissionApiController::class, 'getExistingBid']);
// Legacy endpoint for existing integrations
Route::post('/bid-submissions/legacy', [\App\Http\Controllers\API\Procurement\BidSubmissionApiController::class, 'submitBid']);

// Supplier Management APIs for Tender Creation
Route::get('/suppliers/for-tender', function (Request $request) {
    // Temporary hardcoded data - replace with actual database query when needed
    return response()->json([
        'success' => true,
        'suppliers' => [
            ['id' => 1, 'name' => 'ABC Construction Ltd', 'pin' => 'P001234567', 'email' => 'info@abcconstruction.co.ke', 'category' => 'Construction'],
            ['id' => 2, 'name' => 'TechSolutions Kenya', 'pin' => 'P002345678', 'email' => 'contact@techsolutions.co.ke', 'category' => 'IT Services'],
            ['id' => 3, 'name' => 'Office Supplies Plus', 'pin' => 'P003456789', 'email' => 'sales@officesupplies.co.ke', 'category' => 'Office Supplies']
        ],
        'total' => 3,
        'filtered_by_category' => false
    ]);
});
// Temporarily commented out - SupplierApiController does not exist
// Route::get('/suppliers/categories', [\App\Http\Controllers\API\Procurement\SupplierApiController::class, 'getSupplierCategories']);
// Route::get('/suppliers/portal-registered', [\App\Http\Controllers\API\Procurement\SupplierApiController::class, 'getPortalRegisteredSuppliers']);

Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifiedUser::class])->group(function () {
    Route::get('/thirdpartyuser', function (Request $request) {
        return $request->user();
    })->name('thirdpartyuser.profile');

    Route::prefix('third-party-auth')->group(function () {
        Route::post('logout', [ThirdPartyAuthController::class, 'logout'])->name('third-party-auth.logout');
    });

    Route::prefix('third-party-profile')->group(function () {
        Route::get('/', [ThirdPartyProfileController::class, 'show']);
        Route::put('/', [ThirdPartyProfileController::class, 'update']);
        Route::patch('/', [ThirdPartyProfileController::class, 'partialUpdate']);
        Route::delete('/', [ThirdPartyProfileController::class, 'destroy']);
        Route::put('/password', [ThirdPartyProfileController::class, 'changePassword']);
    });

    Route::prefix('third-parties')->group(function () {
        Route::get('/', [ThirdPartiesController::class, 'index']);

        Route::get('me', [ThirdPartiesController::class, 'showMyThirdPartyDetails']);

        Route::get('{third_party}', [ThirdPartiesController::class, 'show']);
        Route::put('{third_party}', [ThirdPartiesController::class, 'update']);
        Route::delete('{third_party}', [ThirdPartiesController::class, 'destroy']);
        // Upload supporting documents for a third party
        Route::post('{third_party}/documents', [ThirdPartyDocumentsController::class, 'store']);
        // Route::get('suppliers', [ThirdPartyController::class, 'getSuppliers']);
        // Route::patch('{third_party}/approve', [ThirdPartyController::class, 'approve']);
        // Route::patch('{third_party}/reject', [ThirdPartyController::class, 'reject']);
        // Route::patch('{third_party}/status', [ThirdPartyController::class, 'updateStatus']);
    });

    Route::apiResource('third-parties-bank-details', ThirdPartiesBankDetailsController::class);

    Route::middleware('thirdparty.approved')->group(function () {
        Route::apiResource('third-party-categories', ThirdPartyCategoryController::class);
    });

    // Additional tender-related routes (still need auth)
    Route::prefix('tenders')->group(function () {
        Route::post('{tenderId}/items', [TenderApiController::class, 'addItem']);
        Route::delete('{tenderId}/items/{itemId}', [TenderApiController::class, 'deleteItem']);
        Route::post('{tenderId}/suppliers', [TenderApiController::class, 'addSupplier']);
        Route::delete('{tenderId}/suppliers/{supplierId}', [TenderApiController::class, 'deleteSupplier']);
    });
});

// currencies
Route::prefix('v1')->group(function () {
    Route::get('currencies', [ApiCurrencyController::class, 'list']);
});

// countries
Route::prefix('v1')->group(function () {
    Route::get('countries', [\App\Http\Controllers\Settings\Codes\ApiCountryController::class, 'list']);
});

// enums (public)
Route::prefix('enums')->group(function () {
    Route::get('third-party-types', [ThirdPartyTypesEnumController::class, 'index']);
});

Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifiedUser::class])->group(function () {
    Route::prefix('proc')->name('proc.api.')->group(function () {
        Route::apiResource('supplier-cat', SupplierCategoryController::class);
        Route::apiResource('supp', SupplierController::class);
    });
});

Route::prefix('v1')->group(function () {

    require __DIR__ . '/integrations/crm.php';

    require __DIR__ . '/integrations/dms.php';

    Route::prefix('inventory')->group(function () {
        Route::get('item-categories', [\App\Http\Controllers\API\ItemCategories\ItemCategoriesController::class, 'index']);
    });
});


Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifiedUser::class])->prefix('v1')->group(function () {
    Route::get('/prequalification/applications/{roundId}/progress', [PrequalificationProgressController::class, 'getApplicationProgress']);
    Route::post('/prequalification/applications/{roundId}/categories/{categoryId}/progress', [PrequalificationProgressController::class, 'updateCategoryProgress']);
    Route::get('/prequalification/applications/my-applications', [PrequalificationProgressController::class, 'getMyApplications']);
});
// API Routes (for Supplier Portal)
Route::prefix('procurement')->name('api.procurement.')
    ->middleware(['auth:sanctum', \App\Http\Middleware\VerifiedUser::class])
    ->group(function () {
        Route::apiResource('supplier-cat', SupplierCategoryApiController::class);
        Route::apiResource('supp', SupplierController::class);

        // Prequalification API endpoints (for frontend compatibility)
        Route::prefix('prequalification')->name('prequalification.')->group(function () {
            Route::get('rounds', [PrequalificationApplicationController::class, 'apiIndex'])->name('rounds.index');
            Route::get('rounds/{round}', [PrequalificationApplicationController::class, 'apiShow'])->name('rounds.show');
            Route::post('applications', [PrequalificationApplicationController::class, 'store'])->name('applications.store');
        });

        // Supplier RFQ endpoints (supplier portal)
        Route::get('rfq-suppliers', [SupplierRFQController::class, 'listInvitations']);
        Route::get('rfq-suppliers/{rfq}', [SupplierRFQController::class, 'getInvitation'])->whereNumber('rfq');
        Route::post('rfq-responses', [SupplierRFQController::class, 'submitResponse']);
        Route::post('rfq-clarifications', [SupplierRFQController::class, 'postClarification']);
        Route::get('rfq-clarifications/{rfq}', [SupplierRFQController::class, 'listClarifications'])->whereNumber('rfq');
    });

// Prequalification routes (protected) – keep same paths but require auth to align with dashboard usage
Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifiedUser::class])
    ->prefix('prequalification')
    ->name('api.prequalification.')
    ->group(function () {
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
});





/***
 *  This Are the API Routes for Central Report Unit C.R.U
 */

use App\Http\Controllers\API\CRDB\CRDBAuthController;
use App\Http\Controllers\API\CRDB\CRDBGeneralLedgerController;
use App\Http\Controllers\API\CRDB\CRDBCustomerController;
// CRDB Authentication Routes (Public)
Route::prefix('crdb')->group(function () {
    Route::post('login', [CRDBAuthController::class, 'login'])->name('crdb.login');
    Route::post('validate-token', [CRDBAuthController::class, 'validateToken'])->name('crdb.validate-token');
});

// CRDB API Routes (Protected - requires authentication)
Route::prefix('crdb')->middleware(\App\Http\Middleware\CRDBAuthMiddleware::class)->group(function () {
    // Add your CRDB API endpoints here
    // Example:
    Route::get('syncGeneralLedgers', [CRDBGeneralLedgerController::class, 'syncGeneralLedgers'])->name('syncGeneralLedgers');
    Route::get('syncGLBalances', [CRDBGeneralLedgerController::class, 'syncGLBalances'])->name('syncGLBalances');
    Route::get('syncCustomers', [CRDBCustomerController::class, 'syncCustomers'])->name('syncCustomers');
    Route::get('getClientSummaryStatement', [CRDBCustomerController::class, 'getClientSummaryStatement'])->name('getClientSummaryStatement');
    // Route::get('data', [CRDBDataController::class, 'fetch']);

    // Health check for authenticated requests
    Route::get('health', function () {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now(),
            'service' => 'CRDB API',
            'authenticated' => true,
        ]);
    })->name('crdb.health');
});

// Third paties Portal
Route::prefix('v1')->group(function () {
    require __DIR__ . '/portal.php';
});

//api routes for workflow stages
// Route::get('api/workflows/{id}/state', 'Settings\WorkFlowController@getState');
