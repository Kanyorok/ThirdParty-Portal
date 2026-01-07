<?php

use App\Http\Controllers\API\Procurement\SupplierRFQController;
use Illuminate\Support\Facades\Route;

// TEMPORARY TEST ROUTE - NO MIDDLEWARE
Route::get('/test-rfq-no-auth', function() {
    $controller = new SupplierRFQController(
        new \App\Services\Procurement\SupplierRFQService()
    );
    
    // Create a fake authenticated user for testing
    $user = \App\Models\ThirdParty\ThirdPartyUser::first();
    
    if (!$user) {
        return response()->json(['error' => 'No test user found'], 500);
    }
    
    // Manually set auth
    auth('sanctum')->setUser($user);
    
    $request = request();
    return $controller->listInvitations($request);
});
