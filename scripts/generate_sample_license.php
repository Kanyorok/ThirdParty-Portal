<?php
/**
 * Sample License Generator for Testing
 * 
 * This script helps you generate test licenses for development
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel app to access database
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->boot();

if (!extension_loaded('sodium')) {
    die("❌ Error: Sodium extension required\n");
}

echo "🎫 Sample License Generator\n";
echo str_repeat("=", 60) . "\n\n";

// Check if we have the private key
$privateKeyInput = '';
while (empty($privateKeyInput)) {
    echo "🔑 Enter your PRIVATE KEY (base64 encoded): ";
    $privateKeyInput = trim(fgets(STDIN));
    
    if (empty($privateKeyInput)) {
        echo "❌ Private key is required. Please paste your base64 private key.\n";
    }
}

try {
    $privateKeyBinary = base64_decode($privateKeyInput);
    if (strlen($privateKeyBinary) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
        throw new Exception("Invalid private key format");
    }
} catch (Exception $e) {
    die("❌ Invalid private key: " . $e->getMessage() . "\n");
}

// Get current instance information
$instance = \App\Models\Licensing\Instance::current();

echo "\n📋 Current Instance Information:\n";
echo "   DB GUID: {$instance->DbGuid}\n";
echo "   Host Fingerprint: " . substr($instance->HostFingerprint, 0, 20) . "...\n\n";

// License templates
$licenseTemplates = [
    '1' => [
        'name' => 'Community Edition (Basic)',
        'modules' => ['THIRDPARTY', 'SETTINGS', 'ACCOUNT'],
        'max_users' => 5,
        'edition' => 'Community'
    ],
    '2' => [
        'name' => 'Professional Edition (Business)',
        'modules' => ['THIRDPARTY', 'CRM', 'PROCUREMENT', 'INVENTORY', 'FINANCE', 'SETTINGS', 'ACCOUNT'],
        'max_users' => 25,
        'edition' => 'Professional'
    ],
    '3' => [
        'name' => 'Enterprise Edition (Full)',
        'modules' => ['THIRDPARTY', 'CRM', 'PROCUREMENT', 'INVENTORY', 'PROPERTY', 'FLEET', 'DMS', 'LEGAL', 'INSURANCE', 'HRM', 'FINANCE', 'BUDGET', 'SETTINGS', 'ACCOUNT'],
        'max_users' => 100,
        'edition' => 'Enterprise'
    ],
    '4' => [
        'name' => 'Custom License',
        'modules' => [], // Will be filled by user
        'max_users' => 50,
        'edition' => 'Custom'
    ]
];

echo "📦 Available License Templates:\n";
foreach ($licenseTemplates as $key => $template) {
    echo "   {$key}. {$template['name']} ({$template['max_users']} users)\n";
    if ($key !== '4') {
        echo "      Modules: " . implode(', ', $template['modules']) . "\n";
    }
    echo "\n";
}

$choice = '';
while (!isset($licenseTemplates[$choice])) {
    echo "Select license template (1-4): ";
    $choice = trim(fgets(STDIN));
}

$selectedTemplate = $licenseTemplates[$choice];

// Handle custom license
if ($choice === '4') {
    echo "\n🎯 Custom License Configuration:\n";
    
    $availableModules = ['THIRDPARTY', 'CRM', 'PROCUREMENT', 'INVENTORY', 'PROPERTY', 'FLEET', 'DMS', 'LEGAL', 'INSURANCE', 'HRM', 'FINANCE', 'BUDGET', 'SETTINGS', 'ACCOUNT'];
    
    echo "Available modules: " . implode(', ', $availableModules) . "\n";
    echo "Enter modules (comma-separated): ";
    $modulesInput = trim(fgets(STDIN));
    
    $selectedTemplate['modules'] = array_filter(array_map('trim', explode(',', strtoupper($modulesInput))));
    
    echo "Enter max users (default 50): ";
    $maxUsersInput = trim(fgets(STDIN));
    if (!empty($maxUsersInput)) {
        $selectedTemplate['max_users'] = intval($maxUsersInput);
    }
}

// Get license duration
echo "\nSet license duration:\n";
echo "1. 30 days (trial)\n";
echo "2. 1 year\n"; 
echo "3. 2 years\n";
echo "4. Custom\n";
echo "Select duration (1-4): ";
$durationChoice = trim(fgets(STDIN));

$expiryDate = match($durationChoice) {
    '1' => date('c', strtotime('+30 days')),
    '2' => date('c', strtotime('+1 year')),
    '3' => date('c', strtotime('+2 years')),
    '4' => (function() {
        echo "Enter expiry date (YYYY-MM-DD): ";
        $dateInput = trim(fgets(STDIN));
        return date('c', strtotime($dateInput . ' 23:59:59'));
    })(),
    default => date('c', strtotime('+1 year'))
};

// Generate license payload
$licenseId = 'LIC-' . date('Y') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
$tenantName = 'Test Organization';

echo "\nEnter tenant/company name (default: {$tenantName}): ";
$tenantInput = trim(fgets(STDIN));
if (!empty($tenantInput)) {
    $tenantName = $tenantInput;
}

$licensePayload = [
    "license_id" => $licenseId,
    "tenant_name" => $tenantName,
    "tenant_id" => strtolower(str_replace(' ', '-', $tenantName)),
    "edition" => $selectedTemplate['edition'],
    "modules" => $selectedTemplate['modules'],
    "max_users" => $selectedTemplate['max_users'],
    "expires_at" => $expiryDate,
    "issued_at" => date('c'),
    "instance" => [
        "db_guid" => (string) $instance->DbGuid,
        "host_fingerprint" => $instance->HostFingerprint
    ],
    "features" => [
        "drivers_based_budgeting" => in_array('BUDGET', $selectedTemplate['modules']),
        "bancassurance" => in_array('INSURANCE', $selectedTemplate['modules']),
        "advanced_reporting" => $selectedTemplate['edition'] === 'Enterprise',
        "api_access" => $selectedTemplate['max_users'] > 10,
        "mobile_app" => $selectedTemplate['edition'] !== 'Community'
    ],
    "limits" => [
        "branches" => $selectedTemplate['max_users'] > 50 ? 20 : 5,
        "storage_gb" => $selectedTemplate['max_users'] > 25 ? 100 : 10
    ],
    "nonce" => 1
];

// Generate signature
$payloadJson = json_encode($licensePayload, JSON_PRETTY_PRINT);
$signature = sodium_crypto_sign_detached($payloadJson, $privateKeyBinary);
$signatureB64 = base64_encode($signature);

// Create license file
$licenseFile = [
    "payload" => $payloadJson,
    "signature" => $signatureB64
];

$filename = "license_{$licenseId}_" . date('Y-m-d_H-i-s') . ".json";
file_put_contents($filename, json_encode($licenseFile, JSON_PRETTY_PRINT));

echo "\n✅ License generated successfully!\n\n";

echo "📋 License Summary:\n";
echo str_repeat("-", 40) . "\n";
echo "License ID: {$licenseId}\n";
echo "Tenant: {$tenantName}\n";
echo "Edition: {$selectedTemplate['edition']}\n";
echo "Modules: " . implode(', ', $selectedTemplate['modules']) . "\n";
echo "Max Users: {$selectedTemplate['max_users']}\n";
echo "Expires: " . date('Y-m-d H:i:s', strtotime($expiryDate)) . "\n";
echo "Filename: {$filename}\n\n";

echo "🎯 Next Steps:\n";
echo "1. Upload the license via: Settings → Licensing → Upload License\n";
echo "2. Or use the import script: php scripts/import_license.php {$filename}\n";
echo "3. Test module access in the web interface\n\n";

echo "💡 Pro Tips:\n";
echo "- Generate different licenses to test various scenarios\n";
echo "- Try expired licenses to test grace period behavior\n";
echo "- Test with different module combinations\n";
