<?php

use App\Http\Controllers\Procurement\SupplierRFQTestController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| RFQ Test Routes (NO AUTHENTICATION)
|--------------------------------------------------------------------------
|
| ⚠️ WARNING: These routes are for TESTING ONLY and should be removed in production.
| They allow testing RFQ functionality without authentication.
|
| Usage: 
| - Add ?third_party_id=X to test with a specific third party
| - If no third_party_id provided, uses the first available third party
|
| To disable in production, ensure this file is only loaded when:
| env('APP_ENV') !== 'production'
|
*/

Route::prefix('rfq')->name('test.rfq.')->group(function () {
    
    // ========================================================================
    // MAIN RFQ ENDPOINTS (No Auth Required)
    // ========================================================================
    
    /**
     * List all RFQ invitations for a third party
     * GET /api/test/rfq/invitations?third_party_id=1
     */
    Route::get('invitations', [SupplierRFQTestController::class, 'listInvitations'])
        ->name('invitations.list');
    
    /**
     * Get detailed RFQ invitation with items and currencies
     * GET /api/test/rfq/invitations/1?third_party_id=1
     */
    Route::get('invitations/{rfq}', [SupplierRFQTestController::class, 'getInvitation'])
        ->name('invitations.show')
        ->whereNumber('rfq');
    
    /**
     * Submit RFQ response (draft or final)
     * POST /api/test/rfq/responses
     * 
     * Body:
     * {
     *   "third_party_id": 1,
     *   "rfqId": 1,
     *   "currency": "KES",
     *   "durationDays": 30,
     *   "isDraft": false,
     *   "items": [
     *     {
     *       "rfqLineId": 1,
     *       "quotedPrice": 450.00,
     *       "totalPayable": 45000.00
     *     }
     *   ]
     * }
     */
    Route::post('responses', [SupplierRFQTestController::class, 'submitResponse'])
        ->name('responses.submit');
    
    /**
     * Submit clarification question for an RFQ
     * POST /api/test/rfq/clarifications
     * 
     * Body:
     * {
     *   "third_party_id": 1,
     *   "rfqId": 1,
     *   "question": "What is the delivery timeline?",
     *   "rfqLineId": 1
     * }
     */
    Route::post('clarifications', [SupplierRFQTestController::class, 'postClarification'])
        ->name('clarifications.submit');
    
    /**
     * Get all clarifications for an RFQ
     * GET /api/test/rfq/clarifications/1?third_party_id=1
     */
    Route::get('clarifications/{rfq}', [SupplierRFQTestController::class, 'listClarifications'])
        ->name('clarifications.list')
        ->whereNumber('rfq');
    
    // ========================================================================
    // DEBUG ENDPOINTS - Help explore database structure
    // ========================================================================
    
    Route::prefix('debug')->name('debug.')->group(function () {
        
        /**
         * System health check
         * GET /api/test/rfq/debug/health
         */
        Route::get('health', [SupplierRFQTestController::class, 'health'])
            ->name('health');
        
        /**
         * List all third parties
         * GET /api/test/rfq/debug/third-parties
         */
        Route::get('third-parties', [SupplierRFQTestController::class, 'listThirdParties'])
            ->name('third-parties');
        
        /**
         * List suppliers for a third party
         * GET /api/test/rfq/debug/suppliers?third_party_id=1
         */
        Route::get('suppliers', [SupplierRFQTestController::class, 'listSuppliers'])
            ->name('suppliers');
        
        /**
         * List all RFQs
         * GET /api/test/rfq/debug/rfqs
         */
        Route::get('rfqs', [SupplierRFQTestController::class, 'listRFQs'])
            ->name('rfqs');
        
        /**
         * List RFQ invitations (by supplier or RFQ)
         * GET /api/test/rfq/debug/invitations?supplier_id=1
         * GET /api/test/rfq/debug/invitations?rfq_id=1
         */
        Route::get('invitations', [SupplierRFQTestController::class, 'listRFQInvitations'])
            ->name('invitations');
        
        /**
         * Get RFQ lines (items) for a specific RFQ
         * GET /api/test/rfq/debug/rfq-lines/1
         */
        Route::get('rfq-lines/{rfq}', [SupplierRFQTestController::class, 'getRFQLines'])
            ->name('rfq-lines')
            ->whereNumber('rfq');
    });
});

// Quick health check endpoint at root level
Route::get('rfq/health', [SupplierRFQTestController::class, 'health'])
    ->name('test.rfq.health.quick');

    
Route::prefix('procurement')->name('test.procurement.')
    ->group(function () {
        Route::prefix('prequalification')->name('prequalification.')->group(function () {
            
            // Test route to verify data structure without authentication
            Route::get('rounds', function (Request $request) {
                try {
                    // Create a mock authenticated user for testing
                    // You can change this ID to test with different users
                    $testUserId = 1; // Change this to your test user ID
                    
                    $user = \App\Models\Auth\User::find($testUserId);
                    
                    if (!$user) {
                        return response()->json([
                            'error' => 'Test user not found',
                            'message' => 'Please update $testUserId in the route to a valid user ID',
                            'user_id_tried' => $testUserId
                        ], 404);
                    }
                    
                    // Temporarily authenticate as this user
                    Auth::login($user);
                    
                    // Call the actual controller
                    $controller = app(\App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController::class);
                    return $controller->apiIndex($request);
                    
                } catch (\Throwable $e) {
                    return response()->json([
                        'error' => 'Test route failed',
                        'message' => $e->getMessage(),
                        'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode to see trace'
                    ], 500);
                }
            })->name('rounds.index');
            
            Route::get('rounds/{round}', function (Request $request, $roundId) {
                try {
                    $testUserId = 1;
                    $user = \App\Models\Auth\User::find($testUserId);
                    
                    if (!$user) {
                        return response()->json([
                            'error' => 'Test user not found',
                            'user_id_tried' => $testUserId
                        ], 404);
                    }
                    
                    Auth::login($user);
                    
                    $round = \App\Models\Procurement\Prequalification\PrequalificationRound::find($roundId);
                    
                    if (!$round) {
                        return response()->json([
                            'error' => 'Round not found',
                            'round_id' => $roundId
                        ], 404);
                    }
                    
                    $controller = app(\App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController::class);
                    return $controller->apiShow($round);
                    
                } catch (\Throwable $e) {
                    return response()->json([
                        'error' => 'Test route failed',
                        'message' => $e->getMessage(),
                    ], 500);
                }
            })->name('rounds.show');
            
            Route::post('applications', function (Request $request) {
                try {
                    $testUserId = 1;
                    $user = \App\Models\Auth\User::find($testUserId);
                    
                    if (!$user) {
                        return response()->json([
                            'error' => 'Test user not found',
                            'user_id_tried' => $testUserId
                        ], 404);
                    }
                    
                    Auth::login($user);
                    
                    $controller = app(\App\Http\Controllers\Procurement\Prequalification\PrequalificationApplicationController::class);
                    
                    // Create a request instance with validation
                    $formRequest = \App\Http\Requests\Procurement\Suppliers\Prequalification\StorePrequalificationApplicationRequest::createFrom($request);
                    
                    return $controller->store($formRequest);
                    
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return response()->json([
                        'error' => 'Validation failed',
                        'errors' => $e->errors()
                    ], 422);
                } catch (\Throwable $e) {
                    return response()->json([
                        'error' => 'Test route failed',
                        'message' => $e->getMessage(),
                    ], 500);
                }
            })->name('applications.store');
        });
    });