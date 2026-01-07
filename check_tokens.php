<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Checking Personal Access Tokens ===\n\n";

$tokens = DB::table('t_SYSPersonalAccessTokens')
    ->select('id', 'tokenable_type', 'tokenable_id', 'name', 'token', 'created_at')
    ->orderBy('id', 'desc')
    ->limit(5)
    ->get();

foreach ($tokens as $token) {
    echo "ID: {$token->id}\n";
    echo "Tokenable: {$token->tokenable_type} (ID: {$token->tokenable_id})\n";
    echo "Name: {$token->name}\n";
    echo "Token (first 20 chars): " . substr($token->token, 0, 20) . "...\n";
    echo "Created: {$token->created_at}\n";
    echo "---\n";
}
