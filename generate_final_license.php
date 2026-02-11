<?php

require __DIR__ . '/vendor/autoload.php';

// Generate a NEW Keypair
// We cannot reuse the old public key (3Vne...) because we do not have the matching Private Key.
// Digital signatures require the Private Key to sign.
$keyPair = sodium_crypto_sign_keypair();
$secretKey = sodium_crypto_sign_secretkey($keyPair);
$publicKey = sodium_crypto_sign_publickey($keyPair);
$publicKeyBase64 = base64_encode($publicKey);

// Payload with corrected expiration
$payloadData = [
    "instance" => [ "db_guid" => "43099967-C472-401B-BDA4-F21754B59D83" ],
    "modules" => [200000, 100000, 300000, 400000, 1100000, 1000000, 700000, 9800000],
    "nonce" => 1,
    "expires_at" => "2026-03-31T20:59:59Z" // 23:59:59 EAT
];

$jsonPayload = json_encode($payloadData);
$signature = sodium_crypto_sign_detached($jsonPayload, $secretKey);
$signatureBase64 = base64_encode($signature);

echo "=== FINAL LICENSE CREDENTIALS ===\n";
echo "NOTE: You *MUST* update the .env file with the NEW Public Key below.\n";
echo "We cannot use the old key (3Vne...) because its private signing key was not saved.\n\n";

echo "1. UPDATE .ENV (Online Server):\n";
echo "LICENSING_PUBLIC_KEY_BASE64=" . $publicKeyBase64 . "\n\n";

echo "2. ENTER IN ADMIN PANEL:\n";
echo "Payload JSON:\n";
echo $jsonPayload . "\n\n";

echo "Signature (Base64):\n";
echo $signatureBase64 . "\n";
