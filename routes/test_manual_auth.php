<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Procurement\SupplierRFQController;

// Test route that manually authenticates without Sanctum middleware
Route::get('/test-manual-auth', function(\Illuminate\Http\Request $request) {
    try {
        // Manually get token from header
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json(['error' => 'No token provided'], 401);
        }
        
        // Find token in DB
        $accessToken = \Laravel\Sanctum\Sanctum::personalAccessTokenModel()::findToken($token);
        
        if (!$accessToken) {
            return response()->json(['error' => 'Invalid token'], 401);
        }
        
        // Get tokenable WITHOUT triggering relationship
        $userId = $accessToken->tokenable_id;
        $userType = $accessToken->tokenable_type;
        
        // Load user directly
        if ($userType === 'ThirdPartyUser') {
            $user = \App\Models\ThirdParty\ThirdPartyUser::find($userId);
        } else {
            return response()->json(['error' => 'Unsupported user type'], 400);
        }
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 401);
        }
        
        // Now call the service directly
        $service = new \App\Services\Procurement\SupplierRFQService();
        $supplierIds = $service->getSupplierIdsByThirdParty($user->ThirdPartyId);
        $invitations = $service->getInvitationsForSuppliers($supplierIds);
        
        return response()->json([
            'success' => true,
            'data' => $invitations,
            'meta' => [
                'user_id' => $user->Id,
                'manual_auth' => true
            ]
        ]);
        
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file' => $e->getFile() . ':' . $e->getLine()
        ], 500);
    }
});
