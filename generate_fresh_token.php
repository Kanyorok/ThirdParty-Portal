<?php
// Generate a fresh token for testing
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Generating Fresh Token ===\n\n";

$user = \App\Models\ThirdParty\ThirdPartyUser::first();

if (!$user) {
    die("No user found!\n");
}

// Delete old tokens for this user
\Illuminate\Support\Facades\DB::table('t_SYSPersonalAccessTokens')
    ->where('tokenable_type', 'ThirdPartyUser')
    ->where('tokenable_id', $user->Id)
    ->delete();

echo "Deleted old tokens for user {$user->Id}\n";

// Create a fresh token
$token = $user->createToken('postman-test-' . date('Y-m-d-His'));
$plainTextToken = $token->plainTextToken;

echo "\n✓ New Token Created!\n";
echo "==========================================\n";
echo "Token: {$plainTextToken}\n";
echo "==========================================\n";
echo "\nUse this in Postman:\n";
echo "Authorization: Bearer {$plainTextToken}\n";
echo "\nUser Details:\n";
echo "  ID: {$user->Id}\n";
echo "  Name: {$user->FirstName} {$user->LastName}\n";
echo "  Email: {$user->Email}\n";
echo "  ThirdPartyId: {$user->ThirdPartyId}\n";
