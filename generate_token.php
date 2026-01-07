<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use App\Models\ThirdParty\ThirdPartyUser;

echo "=== Creating Fresh API Token ===\n\n";

// Find a ThirdPartyUser
$user = ThirdPartyUser::where('Email', 'raphael.maina@Craftsilicon.com')->first();

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "User: {$user->Email} (ID: {$user->Id})\n\n";

// Delete old tokens
$user->tokens()->delete();
echo "Deleted old tokens\n\n";

// Create new token
$token = $user->createToken('postman-test');
$plainTextToken = $token->plainTextToken;

echo "✅ NEW TOKEN CREATED\n";
echo "==================\n";
echo "Use this EXACT token in Postman:\n\n";
echo "$plainTextToken\n\n";
echo "==================\n";
echo "In Postman:\n";
echo "1. Go to Authorization tab\n";
echo "2. Select 'Bearer Token'\n";
echo "3. Paste the token above\n";
echo "4. Send request\n";
