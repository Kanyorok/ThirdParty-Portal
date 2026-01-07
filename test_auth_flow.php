<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\ThirdParty\ThirdPartyUser;
use App\Models\Auth\PersonalAccessToken;

echo "=== Testing Sanctum Token Flow ===\n\n";

// Test 1: Check if findToken exists
echo "1. Testing PersonalAccessToken::findToken() method:\n";
try {
    $tokenModel = \Laravel\Sanctum\Sanctum::personalAccessTokenModel();
    echo "   Token Model Class: " . $tokenModel . "\n";
    
    if (method_exists($tokenModel, 'findToken')) {
        echo "   ✓ findToken method exists\n";
    } else {
        echo "   ✗ findToken method NOT found\n";
    }
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
}

// Test 2: Get a sample user and check withAccessToken
echo "\n2. Testing ThirdPartyUser->withAccessToken() method:\n";
try {
    $user = ThirdPartyUser::first();
    
    if ($user) {
        echo "   Found user: {$user->Email}\n";
        
        if (method_exists($user, 'withAccessToken')) {
            echo "   ✓ withAccessToken method exists\n";
            
            // Test calling it with a transient token
            $transientToken = new \Laravel\Sanctum\TransientToken();
            $userWithToken = $user->withAccessToken($transientToken);
            echo "   ✓ Successfully called withAccessToken\n";
            echo "   User class: " . get_class($userWithToken) . "\n";
        } else {
            echo "   ✗ withAccessToken method NOT found\n";
            echo "   Available methods: " . implode(', ', array_slice(get_class_methods($user), 0, 10)) . "...\n";
        }
    } else {
        echo "   No ThirdPartyUser found in database\n";
    }
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 3: Check tokens relationship
echo "\n3. Testing tokens() relationship:\n";
try {
    $user = ThirdPartyUser::first();
    
    if ($user) {
        if (method_exists($user, 'tokens')) {
            echo "   ✓ tokens() method exists\n";
            
            $tokensRelation = $user->tokens();
            echo "   Relationship type: " . get_class($tokensRelation) . "\n";
            
            $tokenCount = $user->tokens()->count();
            echo "   Token count for user: {$tokenCount}\n";
        } else {
            echo "   ✗ tokens() method NOT found\n";
        }
    }
} catch (\Exception $e) {
    echo "   Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Test 4: Check trait usage
echo "\n4. Checking trait usage:\n";
$traits = class_uses_recursive(ThirdPartyUser::class);
if (in_array('Laravel\Sanctum\HasApiTokens', $traits)) {
    echo "   ✓ HasApiTokens trait is used\n";
} else {
    echo "   ✗ HasApiTokens trait NOT found\n";
}

echo "\nDone!\n";
