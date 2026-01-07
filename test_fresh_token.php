<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Auth\PersonalAccessToken;

echo "=== Creating Fresh Token and Testing Guard Flow ===\n\n";

// Get a user
$user = ThirdPartyUser::first();

if (!$user) {
    echo "ERROR: No ThirdPartyUser found in database\n";
    exit(1);
}

echo "User: {$user->Email} (ID: {$user->Id})\n";

// Delete old tokens for this test
$user->tokens()->delete();
echo "Deleted old tokens\n\n";

// Create a new token
echo "Creating new token...\n";
$tokenResult = $user->createToken('test-guard-flow');
$plainTextToken = $tokenResult->plainTextToken;
$tokenId = $tokenResult->accessToken->id;

echo "✓ Token created\n";
echo "  Token ID: {$tokenId}\n";
echo "  Plain text token: " . substr($plainTextToken, 0, 20) . "...\n\n";

// Now test the Guard flow
echo "=== Simulating Sanctum Guard Flow ===\n\n";

try {
    // Step 1: Find token using findToken (like Guard does at line 60)
    $model = \Laravel\Sanctum\Sanctum::personalAccessTokenModel();
    echo "1. Finding token using {$model}::findToken()\n";
    
    $accessToken = $model::findToken($plainTextToken);
    
    if (!$accessToken) {
        echo "   ✗ Token not found!\n";
        echo "   Checking database directly...\n";
        $dbToken = PersonalAccessToken::find($tokenId);
        echo "   DB Token exists: " . ($dbToken ? 'Yes' : 'No') . "\n";
        if ($dbToken) {
            echo "   DB Token details:\n";
            echo "     - ID: {$dbToken->id}\n";
            echo "     - tokenable_type: {$dbToken->tokenable_type}\n";
            echo "     - tokenable_id: {$dbToken->tokenable_id}\n";
        }
        exit(1);
    }
    
    echo "   ✓ Token found\n";
    echo "     - Token ID: {$accessToken->id}\n";
    echo "     - Tokenable Type: {$accessToken->tokenable_type}\n";
    echo "     - Tokenable ID: {$accessToken->tokenable_id}\n\n";
    
    // Step 2: Get tokenable (like Guard does)
    echo "2. Loading tokenable relationship\n";
    $tokenable = $accessToken->tokenable;
    
    if (!$tokenable) {
        echo "   ✗ Tokenable is NULL!\n";
        echo "   This means the relationship is broken\n";
        exit(1);
    }
    
    echo "   ✓ Tokenable loaded\n";
    echo "     - Class: " . get_class($tokenable) . "\n";
    echo "     - Email: {$tokenable->Email}\n";
    echo "     - ID: {$tokenable->Id}\n\n";
    
    // Step 3: Call withAccessToken (THIS IS LINE 59 IN GUARD)
    echo "3. Calling tokenable->withAccessToken() [LINE 59 IN GUARD]\n";
    
    if (!method_exists($tokenable, 'withAccessToken')) {
        echo "   ✗ METHOD NOT FOUND!\n";
        echo "   Available methods: " . implode(', ', array_slice(get_class_methods($tokenable), 0, 15)) . "...\n";
        exit(1);
    }
    
    $authenticatedUser = $tokenable->withAccessToken($accessToken);
    
    echo "   ✓ withAccessToken() called successfully!\n";
    echo "     - Returned class: " . get_class($authenticatedUser) . "\n";
    echo "     - Has current token: " . ($authenticatedUser->currentAccessToken() ? 'Yes' : 'No') . "\n\n";
    
    echo "✅ SUCCESS! The Sanctum Guard flow works correctly!\n";
    echo "\nThe fix is working. The error you're seeing must be from a different cause.\n";
    
} catch (\Throwable $e) {
    echo "\n❌ ERROR:\n";
    echo "   Message: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n   Stack trace:\n";
    echo "   " . str_replace("\n", "\n   ", $e->getTraceAsString()) . "\n";
    exit(1);
}
