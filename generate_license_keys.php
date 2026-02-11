<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// 1. Generate new Keypair
$keyPair = sodium_crypto_sign_keypair();
$secretKey = sodium_crypto_sign_secretkey($keyPair);
$publicKey = sodium_crypto_sign_publickey($keyPair);
$publicKeyBase64 = base64_encode($publicKey);

echo "----------------------------------------------------------------\n";
echo "NEW LICENSING_PUBLIC_KEY_BASE64:\n";
echo $publicKeyBase64 . "\n";
echo "----------------------------------------------------------------\n";
echo "IMPORTANT: You MUST update your .env file with this new key.\n";
echo "----------------------------------------------------------------\n";

// 2. Prepare Payload
// User wanted "2026-03-31T23:59:59Z". In EAT (UTC+3), this shows as April 1st.
// Adjusted to "2026-03-31T20:59:59Z" so it displays as "2026-03-31 23:59:59" in EAT.
$expiresAt = "2026-03-31T20:59:59Z";

$payloadData = [
    "instance" => [ "db_guid" => "43099967-C472-401B-BDA4-F21754B59D83" ],
    // Added 100000 as requested
    "modules" => [200000, 100000, 300000, 400000, 1100000, 1000000, 700000, 9800000],
    "nonce" => 1,
    "expires_at" => $expiresAt
];

$jsonPayload = json_encode($payloadData);
$signature = sodium_crypto_sign_detached($jsonPayload, $secretKey);
$signatureBase64 = base64_encode($signature);

// 3. Revoke old licenses
// Schema uses 'Status' (1=active, 0=revoked)
DB::table('t_Licenses')->update(['Status' => 0]);

// 4. Insert New License
DB::table('t_Licenses')->insert([
    'LicenseId' => (string) Str::uuid(),
    'PayloadJson' => $jsonPayload,
    'SignatureBase64' => $signatureBase64,
    'PublicKeyId' => 'vendor-key-1', // Matching .env default
    'Status' => 1, // Active
    'CreatedOn' => now(),
    // 'LastValidatedOn' => null
]);

echo "License inserted successfully into database.\n";
echo "Modules: " . implode(', ', $payloadData['modules']) . "\n";
echo "Expires (UTC): " . $payloadData['expires_at'] . "\n";
echo "Expires (EAT): 2026-03-31 23:59:59\n";
