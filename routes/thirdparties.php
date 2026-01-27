<?php

use App\Http\Controllers\API\Enums\ThirdPartyTypesEnumController;
use App\Http\Controllers\API\ThirdParty\ThirdPartiesBankDetailsController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyCategoryController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyDocumentsController;
use App\Http\Controllers\API\ThirdParty\ThirdPartyProfileController;
use App\Http\Controllers\Procurement\ThirdParties\ThirdPartiesController;
use App\Http\Controllers\Procurement\ThirdParties\ThirdPartyAuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Third Party Routes
|--------------------------------------------------------------------------
|
*/

Route::prefix('portal/auth')->group(function () {
    Route::post('login', [ThirdPartyAuthController::class, 'login']);
    Route::post('register', [ThirdPartyAuthController::class, 'register']); // Step 1: User personal registration
    Route::get('/email/verify/{id}/{hash}', [ThirdPartyAuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/resend-verification', [ThirdPartyAuthController::class, 'resendVerification'])->name('verification.resend')->middleware('throttle:6,1');
});

// Step 2: Register company info (associated third party) - NO AUTH REQUIRED FOR INITIAL SETUP
Route::post('third-parties/register-details', [ThirdPartiesController::class, 'store']);

// --- General Public / Unprotected Third Party-related Endpoints ---

// Enums (public)
Route::prefix('enums')->group(function () {
    Route::get('third-party-types', [ThirdPartyTypesEnumController::class, 'index']);
});

// Test endpoint for debugging (NO AUTH REQUIRED)
Route::get('/debug/tender-invitations', function (Illuminate\Http\Request $request) {
    try {
        $thirdPartyId = $request->query('third_party_id', 1); // Default to ID 1 for testing

        // Get supplier ID from third party ID
        $supplier = \App\Models\ThirdParies\Supplier::whereHas('thirdParty', function ($query) use ($thirdPartyId) {
            $query->where('Id', $thirdPartyId);
        })->first();

        if (! $supplier) {
            return response()->json([
                'debug' => 'No supplier found',
                'third_party_id' => $thirdPartyId,
                'third_parties_count' => DB::table('t_ThirdParties')->count(),
                'suppliers_count' => DB::table('t_Suppliers')->count(),
                'sample_third_party' => DB::table('t_ThirdParties')->first(),
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
                    'tender_title' => $inv->tender ? $inv->tender->Title : 'No tender loaded',
                ];
            }),
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'debug' => 'Error in debug endpoint',
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
});


// --- Authenticated Third Party Routes (Requires auth:sanctum & VerifiedUser middleware) ---

Route::middleware(['auth:sanctum', \App\Http\Middleware\VerifiedUser::class])->group(function () {

    // Token validation for session checks
    Route::post('auth/validate-token', function (Request $request) {
        $user = $request->user();
        if (! $user) {
            return response()->json(['valid' => false], 401);
        }

        // ThirdPartyUser specific flags
        $isActive = method_exists($user, 'isActive') ? $user->isActive() : (bool)($user->IsActive ?? $user->isActive ?? false);
        $isApproved = method_exists($user, 'isApproved') ? $user->isApproved() : (bool)($user->isApproved ?? false);

        if (! $isActive || ! $isApproved) {
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
    })->name('auth.validate-token');

    // Get the authenticated Third Party User object
    Route::get('/thirdpartyuser', function (Request $request) {
        return $request->user();
    })->name('thirdpartyuser.profile');

    // Logout
    Route::prefix('third-party-auth')->group(function () {
        Route::post('logout', [ThirdPartyAuthController::class, 'logout'])->name('third-party-auth.logout');
    });

    // Profile Management (The user's individual account)
    Route::prefix('third-party-profile')->group(function () {
        Route::get('/', [ThirdPartyProfileController::class, 'show']);
        Route::put('/', [ThirdPartyProfileController::class, 'update']);
        Route::patch('/', [ThirdPartyProfileController::class, 'partialUpdate']);
        Route::delete('/', [ThirdPartyProfileController::class, 'destroy']);
        Route::put('/password', [ThirdPartyProfileController::class, 'changePassword']);
    });

    // Third Party Company/Organization Details Management
    Route::prefix('third-parties')->group(function () {
        Route::get('/', [ThirdPartiesController::class, 'index']); // List all (Admin/Internal use)

        // Get the authenticated user's associated Third Party company details
        Route::get('me', [ThirdPartiesController::class, 'showMyThirdPartyDetails']);

        Route::get('{third_party}', [ThirdPartiesController::class, 'show']);
        Route::put('{third_party}', [ThirdPartiesController::class, 'update']);
        Route::delete('{third_party}', [ThirdPartiesController::class, 'destroy']);

        // Upload supporting documents for a third party
        Route::post('{third_party}/documents', [ThirdPartyDocumentsController::class, 'store']);
    });

    // Third Party Bank Details (API Resource)
    Route::apiResource('third-parties-bank-details', ThirdPartiesBankDetailsController::class);

    // Third Party Categories (Requires thirdparty.approved middleware)
    Route::middleware('thirdparty.approved')->group(function () {
        Route::apiResource('third-party-categories', ThirdPartyCategoryController::class);
    });

    // Procurement Routes for Supplier Portal

    Route::prefix('procurement')->name('api.procurement.')->group(function () {

        // Prequalification API endpoints (for frontend compatibility)
        Route::prefix('prequalification')->name('prequalification.')->group(function () {
            // Controller not included in the 'use' statements, but path is clearly third-party/procurement
            // Route::get('rounds', [PrequalificationApplicationController::class, 'apiIndex'])->name('rounds.index');
            // Route::get('rounds/{round}', [PrequalificationApplicationController::class, 'apiShow'])->name('rounds.show');
            // Route::post('applications', [PrequalificationApplicationController::class, 'store'])->name('applications.store');
        });

        // Supplier RFQ endpoints (supplier portal)
        // Controller not included in the 'use' statements, but path is clearly third-party/procurement
        // Route::get('rfq-suppliers', [SupplierRFQController::class, 'listInvitations']);
        // Route::get('rfq-suppliers/{rfq}', [SupplierRFQController::class, 'getInvitation'])->whereNumber('rfq');
        // Route::post('rfq-responses', [SupplierRFQController::class, 'submitResponse']);
        // Route::post('rfq-clarifications', [SupplierRFQController::class, 'postClarification']);
        // Route::get('rfq-clarifications/{rfq}', [SupplierRFQController::class, 'listClarifications'])->whereNumber('rfq');
    });

    // Prequalification Progress (Third Party Application State)
    Route::prefix('v1')->group(function () {
        // Controller not included in the 'use' statements, but path is clearly third-party/procurement
        // Route::get('/prequalification/applications/{roundId}/progress', [PrequalificationProgressController::class, 'getApplicationProgress']);
        // Route::post('/prequalification/applications/{roundId}/categories/{categoryId}/progress', [PrequalificationProgressController::class, 'updateCategoryProgress']);
        // Route::get('/prequalification/applications/my-applications', [PrequalificationProgressController::class, 'getMyApplications']);
    });
});
