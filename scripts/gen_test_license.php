<?php

// Usage: php scripts/gen_test_license.php > /tmp/license.out

$modules = [200000, 300000, 400000];
$payload = [
	'license_id' => 'LIC-TEST-'.date('Ymd-His'),
	'tenant' => ['name' => 'Test Tenant', 'id' => 'test-tenant'],
	'edition' => 'Pro',
	'modules' => $modules,
	'max_users' => 50,
	'issued_at' => gmdate('c'),
	'expires_at' => gmdate('Y-m-d\TH:i:s\Z', strtotime('+30 days')),
	'instance' => [
		'db_guid' => 'REPLACE_DB_GUID',
		'host_fingerprint' => php_uname('n')."|".php_uname('s')."|".php_uname('r')
	],
	'features' => ['bancassurance' => true],
	'limits' => ['branches' => 40],
	'nonce' => 1,
	'kid' => 'vendor-key-1'
];

$payloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);

$keypair = sodium_crypto_sign_keypair();
$publicKey = sodium_crypto_sign_publickey($keypair);
$secretKey = sodium_crypto_sign_secretkey($keypair);

$signature = sodium_crypto_sign_detached($payloadJson, $secretKey);

echo "PUBLIC_KEY_BASE64=".base64_encode($publicKey)."\n";

echo "PAYLOAD_JSON<<\n".$payloadJson."\n<<\n";

echo "SIGNATURE_BASE64=".base64_encode($signature)."\n";