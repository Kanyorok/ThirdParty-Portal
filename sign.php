<?php
$payload = file_get_contents('payload.json');
$sk = base64_decode(getenv('VENDOR_PRIVATE_KEY_BASE64'), true);
if ($sk === false) { fwrite(STDERR, "Bad private key base64\n"); exit(1); }
if (!function_exists('sodium_crypto_sign_detached')) { fwrite(STDERR, "Sodium missing\n"); exit(1); }
$sig = sodium_crypto_sign_detached($payload, $sk);
file_put_contents('signature.b64', base64_encode($sig));
