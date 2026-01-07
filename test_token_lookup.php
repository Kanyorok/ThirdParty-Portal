<?php
// Test if Sanctum can find the token
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$plainTextToken = '8|JgeNNEumGWvCbsO4v7wTVF274OziwG13W4jxmPnMd84c7c08';

echo "=== Testing Token Lookup ===\n\n";

$tokenModel = \Laravel\Sanctum\Sanctum::personalAccessTokenModel();
echo "Token Model: $tokenModel\n\n";

echo "Attempting to find token...\n";
$accessToken = $tokenModel::findToken($plainTextToken);

if ($accessToken) {
    echo "✓ Token found!\n";
    echo "  ID: {$accessToken->id}\n";
    echo "  Type: {$accessToken->tokenable_type}\n";
    echo "  User ID: {$accessToken->tokenable_id}\n";
    echo "  Name: {$accessToken->name}\n";
    
    echo "\nAttempting to load tokenable...\n";
    $user = $accessToken->tokenable;
    
    if ($user) {
        echo "✓ User loaded!\n";
        echo "  Class: " . get_class($user) . "\n";
        echo "  ID: {$user->Id}\n";
        echo "  Name: {$user->FirstName} {$user->LastName}\n";
    } else {
        echo "✗ Tokenable is NULL\n";
    }
} else {
    echo "✗ Token NOT found!\n";
    
    // Check if it exists in DB
    $parts = explode('|', $plainTextToken, 2);
    if (count($parts) === 2) {
        $id = $parts[0];
        $token = $parts[1];
        
        echo "\nChecking DB directly for token ID: $id\n";
        $dbToken = \Illuminate\Support\Facades\DB::table('t_SYSPersonalAccessTokens')
            ->where('id', $id)
            ->first();
        
        if ($dbToken) {
            echo "✓ Found in DB:\n";
            echo "  ID: {$dbToken->id}\n";
            echo "  Type: {$dbToken->tokenable_type}\n";
            echo "  Hash: " . substr($dbToken->token, 0, 20) . "...\n";
            
            // Verify hash
            $expectedHash = hash('sha256', $token);
            echo "\nToken hash match: " . ($dbToken->token === $expectedHash ? 'YES' : 'NO') . "\n";
        } else {
            echo "✗ Not found in DB\n";
        }
    }
}
