<?php

if (!function_exists('sodium_crypto_sign_keypair')) {
    fwrite(STDERR, 'Sodium missing' . PHP_EOL);
    exit(1);
}
$kp = sodium_crypto_sign_keypair();
echo 'PRIVATE_KEY_BASE64=' . base64_encode(sodium_crypto_sign_secretkey($kp)) . PHP_EOL;
echo 'PUBLIC_KEY_BASE64=' . base64_encode(sodium_crypto_sign_publickey($kp)) . PHP_EOL;
