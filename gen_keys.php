<?php
if (!function_exists('sodium_crypto_sign_keypair')) { fwrite(STDERR,"Sodium missing\n"); exit(1); }
$kp = sodium_crypto_sign_keypair();
$sk = sodium_crypto_sign_secretkey($kp); // 64 bytes (libsodium format)
$pk = sodium_crypto_sign_publickey($kp); // 32 bytes
echo "PRIVATE_KEY_BASE64=".base64_encode($sk).PHP_EOL;
echo "PUBLIC_KEY_BASE64=".base64_encode($pk).PHP_EOL;
