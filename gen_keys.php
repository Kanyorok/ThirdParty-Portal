<?php
if (!function_exists('sodium_crypto_sign_keypair')) { fwrite(STDERR,"Sodium missing\n"); exit(1); }

$kp = sodium_crypto_sign_keypair();
$sk = sodium_crypto_sign_secretkey($kp); // 64 bytes
$pk = sodium_crypto_sign_publickey($kp); // 32 bytes

echo "PRIVATE_KEY_BASE64=" . base64_encode($sk) . PHP_EOL;
echo "PUBLIC_KEY_BASE64="  . base64_encode($pk) . PHP_EOL;

$payloadPath = $argv[1] ?? __DIR__ . '/payload.json';
if (is_file($payloadPath)) {
    $payload = file_get_contents($payloadPath);
    if ($payload === false) { fwrite(STDERR, "Cannot read $payloadPath\n"); exit(1); }

    // Sign with the secret key (no need to build a keypair again)
    $sig = sodium_crypto_sign_detached($payload, $sk);
    $sigB64 = base64_encode($sig);

    // Print and save
    echo "SIGNATURE_BASE64=" . $sigB64 . PHP_EOL;
    file_put_contents(__DIR__ . '/signature.b64', $sigB64 . PHP_EOL);

    // Optional: verify immediately
    $ok = sodium_crypto_sign_verify_detached($sig, $payload, $pk);
    echo "VERIFY=" . ($ok ? "true" : "false") . PHP_EOL;
}
