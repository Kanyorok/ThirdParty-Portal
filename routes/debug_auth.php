<?php

use Illuminate\Support\Facades\Route;

// Debug route to test Sanctum auth
Route::middleware(['auth:sanctum'])->get('/debug-auth', function(\Illuminate\Http\Request $request) {
    $user = $request->user();
    
    return response()->json([
        'authenticated' => !!$user,
        'user' => $user ? [
            'id' => $user->Id,
            'class' => get_class($user),
            'name' => $user->FirstName . ' ' . $user->LastName,
        ] : null,
        'bearer_token_present' => $request->bearerToken() !== null,
    ]);
});
