<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Auth\PersonalAccessToken;

echo "=== Testing Actual Sanctum Guard Flow ===\n\n";

// Get a user with a token
$user = ThirdPartyUser::whereHas('tokens')->first();

if (!$user) {
    echo "No user with tokens found. Creating test token...\n";
    $user = ThirdPartyUser::first();
    
    if (!$user) {
        echo "ERROR: No ThirdPartyUser found in database\n";
        exit(1);
    }
    
    // Create a test token
    $token = $user->createToken('test-token');
    echo "Created test token for user: {$user->Email}\n";
    $plainTextToken = $token->plainTextToken;
} else {
    echo "Found user with existing tokens: {$user->Email}\n";
    // Get the first token
    $accessToken = $user->tokens()->first();
    $plainTextToken = $accessToken->id . '|' . 'dummy-token-for-testing';
}

echo "\n=== Simulating Sanctum Guard Authentication ===\n";

try {
    // Simulate what the Guard does
    $tokenString = explode('|', $plainTextToken)[1] ?? $plainTextToken;
    
    // Step 1: Find token (like Guard does)
    $model = \Laravel\Sanctum\Sanctum::personalAccessTokenModel();
    echo "1. Token Model: $model\n";
    
    $accessToken = $model::findToken($plainTextToken);
    
    if (!$accessToken) {
        echo "   ✗ Token not found\n";
        exit(1);
    }
    
    echo "   ✓ Token found (ID: {$accessToken->id})\n";
    
    // Step 2: Get tokenable (like Guard does)
    echo "\n2. Getting tokenable:\n";
    echo "   Tokenable Type: {$accessToken->tokenable_type}\n";
    echo "   Tokenable ID: {$accessToken->tokenable_id}\n";
    
    $tokenable = $accessToken->tokenable;
    
    if (!$tokenable) {
        echo "   ✗ Tokenable not found\n";
        exit(1);
    }
    
    echo "   ✓ Tokenable loaded: " . get_class($tokenable) . "\n";
    echo "   User Email: {$tokenable->Email}\n";
    
    // Step 3: Call withAccessToken (like Guard does at line 59)
    echo "\n3. Calling withAccessToken (THIS IS WHERE THE ERROR OCCURS):\n";
    
    if (!method_exists($tokenable, 'withAccessToken')) {
        echo "   ✗ withAccessToken method NOT FOUND on tokenable\n";
        echo "   Tokenable class: " . get_class($tokenable) . "\n";
        echo "   Traits: " . implode(', ', array_keys(class_uses($tokenable))) . "\n";
        exit(1);
    }
    
    $authenticatedUser = $tokenable->withAccessToken($accessToken);
    
    echo "   ✓ withAccessToken called successfully\n";
    echo "   Authenticated user: " . get_class($authenticatedUser) . "\n";
    echo "   Current access token set: " . ($authenticatedUser->currentAccessToken() ? 'Yes' : 'No') . "\n";
    
    echo "\n✅ ALL TESTS PASSED - Sanctum Guard flow works correctly!\n";
    
} catch (\Throwable $e) {
    echo "\n❌ ERROR OCCURRED:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "   Trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
