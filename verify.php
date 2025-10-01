<?php
$payload = file_get_contents(__DIR__ . '/payload.json');
$pub     = base64_decode(getenv('LICENSING_PUBLIC_KEY_BASE64') ?: '');
$sig     = base64_decode(trim(file_get_contents(__DIR__ . '/signature.b64')));

if ($payload === false) { fwrite(STDERR, "Missing payload.json\n"); exit(1); }
if (!$pub) { fwrite(STDERR, "LICENSING_PUBLIC_KEY_BASE64 not set\n"); exit(1); }

var_dump(sodium_crypto_sign_verify_detached($sig, $payload, $pub));
