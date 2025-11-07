<?php
$priv = base64_decode(trim(file_get_contents('ed25519-priv.key')));
$payload = file_get_contents('payload.json');
$payload = json_encode(json_decode($payload, true), JSON_UNESCAPED_SLASHES); // stable encoding
$sig = sodium_crypto_sign_detached($payload, $priv);
echo base64_encode($sig), PHP_EOL;