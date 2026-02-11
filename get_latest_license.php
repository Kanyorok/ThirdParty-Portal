<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$license = DB::table('t_Licenses')
    ->orderBy('CreatedOn', 'desc')
    ->first();

if (!$license) {
    echo "No license found.\n";
    exit(1);
}

echo "=== LATEST LICENSE DETAILS ===\n";
echo "Use these values for your online build:\n\n";

echo "--- Payload JSON ---\n";
echo $license->PayloadJson . "\n\n";

echo "--- Signature (Base64) ---\n";
echo $license->SignatureBase64 . "\n\n";

echo "--- Public Key (Base64) ---\n";
// The public key might be stored in a separate column or config, but we printed it earlier.
// If t_Licenses doesn't store the full public key string (it stores ID), we rely on the previous output.
// But wait, the previous output GAVE the public key.
// The user specifically asked "where is the signature".
