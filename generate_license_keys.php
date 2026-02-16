<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "--- License Generation Started ---\n";

// 1. Fetch Instance GUID
$instance = DB::table('t_Instance')->orderBy('Id')->first();
if (!$instance || empty($instance->DbGuid)) {
    die("ERROR: Could not find Instance GUID in t_Instance table.\n");
}
$dbGuid = $instance->DbGuid;
echo "Using Instance GUID: " . $dbGuid . "\n";

// 2. Generate new Keypair
$keyPair = sodium_crypto_sign_keypair();
$secretKey = sodium_crypto_sign_secretkey($keyPair);
$publicKey = sodium_crypto_sign_publickey($keyPair);
$publicKeyBase64 = base64_encode($publicKey);

echo "Generated Public Key: " . $publicKeyBase64 . "\n";

// 3. Update .env (Optional but recommended)
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    $envContent = file_get_contents($envPath);
    // Replace existing key or add if missing
    if (preg_match('/^LICENSING_PUBLIC_KEY_BASE64=.*$/m', $envContent)) {
        $envContent = preg_replace('/^LICENSING_PUBLIC_KEY_BASE64=.*$/m', 'LICENSING_PUBLIC_KEY_BASE64=' . $publicKeyBase64, $envContent);
    } else {
        $envContent .= "\nLICENSING_PUBLIC_KEY_BASE64=" . $publicKeyBase64 . "\n";
    }
    file_put_contents($envPath, $envContent);
    echo "Updated .env with new Public Key.\n";
} else {
    echo "WARNING: .env file not found at $envPath. You must update it manually.\n";
}

// 4. Prepare Payload
// Expires: 2026-03-31 23:59:59 EAT (UTC+3) -> 20:59:59 UTC
$expiresAt = "2026-03-31T20:59:59Z";

$payloadData = [
    "instance" => [ "db_guid" => $dbGuid ],
    "modules" => [
        200000, 100000, 300000, 400000, 1100000, 1000000, 700000, 9800000, 9900000
    ],
    "nonce" => 1,
    "expires_at" => $expiresAt
];

$jsonPayload = json_encode($payloadData);
$signature = sodium_crypto_sign_detached($jsonPayload, $secretKey);
$signatureBase64 = base64_encode($signature);

// 5. Revoke old licenses
echo "Revoking old licenses...\n";
DB::table('t_Licenses')->update(['Status' => 0]);

// 6. Insert New License
echo "Inserting new license...\n";
try {
    $inserted = DB::table('t_Licenses')->insert([
        'LicenseId' => (string) Str::uuid(),
        'PayloadJson' => $jsonPayload,
        'SignatureBase64' => $signatureBase64,
        'PublicKeyId' => 'vendor-key-1', 
        'Status' => 1, // Active
        'CreatedOn' => now(),
        'LastValidatedOn' => null // Reset validation
    ]);
    
    if ($inserted) {
        echo "SUCCESS: License inserted successfully.\n";
        echo "Valid for Instance: " . $dbGuid . "\n";
        echo "Expires: " . $expiresAt . " (UTC)\n";
    } else {
        echo "ERROR: Insert query returned false.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: Failed to insert license: " . $e->getMessage() . "\n";
}

echo "----------------------------------------------------------------\n";
