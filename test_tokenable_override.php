<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing PersonalAccessToken tokenable override...\n";

$token = \App\Models\Auth\PersonalAccessToken::find(8);

if (!$token) {
    die("Token not found\n");
}

echo "Token ID: {$token->id}\n";
echo "Tokenable type: {$token->tokenable_type}\n";
echo "Tokenable ID: {$token->tokenable_id}\n";

echo "\nAccessing tokenable...\n";
$user = $token->tokenable;

if ($user) {
    echo "SUCCESS! User loaded: " . get_class($user) . "\n";
    echo "User ID: {$user->Id}\n";
    echo "User name: {$user->FirstName} {$user->LastName}\n";
} else {
    echo "FAILED! Tokenable is null\n";
}

echo "\nPeak memory: " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB\n";
