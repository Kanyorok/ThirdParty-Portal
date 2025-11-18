<?php
if (!function_exists("sodium_crypto_sign_keypair")) {
    fwrite(STDERR, "Sodium missing\n");
    exit(1);
}
$kp = sodium_crypto_sign_keypair();
echo "PUBLIC_KEY_BASE64=" . base64_encode(sodium_crypto_sign_publickey($kp)) . PHP_EOL;

