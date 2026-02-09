<?php

$payloadJson = '{
  "instance": { "db_guid": "43099967-C472-401B-BDA4-F21754B59D83" },
  "modules": [200000, 300000, 400000, 1100000, 1000000, 700000, 9800000],
  "nonce": 1,
  "expires_at": "2026-03-31T23:59:59Z"
}';

// Decode and re-encode to ensure it's minified/consistent if we wanted, 
// but let's just use the string as-is to be safe, or just clean it up 
// so the user can copy-paste it easily.
// Actually, let's minify it for the signature to avoid whitespace issues during copy-paste.
$decoded = json_decode($payloadJson);
if ($decoded === null) {
    echo "Error: Invalid JSON\n";
    exit(1);
}
$payload = json_encode($decoded);

$keyPair = sodium_crypto_sign_keypair();
$secretKey = sodium_crypto_sign_secretkey($keyPair);
$publicKey = sodium_crypto_sign_publickey($keyPair);

$signature = sodium_crypto_sign_detached($payload, $secretKey);

echo "=== INSTRUCTIONS ===\n";
echo "1. Update your .env file with the following Public Key:\n";
echo "LICENSING_PUBLIC_KEY_BASE64=" . base64_encode($publicKey) . "\n";
echo "LICENSING_PUBLIC_KEY_ID=vendor-key-1\n\n";

echo "2. Use the following values in the Admin > Licensing form:\n\n";

echo "--- Payload JSON (Minified) ---\n";
echo $payload . "\n\n";

echo "--- Signature (Base64) ---\n";
echo base64_encode($signature) . "\n";
