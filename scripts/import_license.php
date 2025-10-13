<?php
/**
 * License Import Script
 * 
 * Quickly import a license file without using the web interface
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->boot();

if (count($argv) < 2) {
    echo "Usage: php scripts/import_license.php <license_file.json>\n";
    echo "Example: php scripts/import_license.php license_LIC-2025-00123_2025-09-24_15-30-45.json\n";
    exit(1);
}

$licenseFile = $argv[1];

if (!file_exists($licenseFile)) {
    die("❌ Error: License file '{$licenseFile}' not found\n");
}

echo "🎫 Importing License: {$licenseFile}\n";
echo str_repeat("=", 60) . "\n\n";

try {
    // Read and parse license file
    $content = file_get_contents($licenseFile);
    $licenseData = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

    if (!isset($licenseData['payload'], $licenseData['signature'])) {
        throw new Exception('Invalid license file format - missing payload or signature');
    }

    $payload = is_string($licenseData['payload']) 
        ? $licenseData['payload'] 
        : json_encode($licenseData['payload']);
        
    $signature = $licenseData['signature'];
    
    // Extract license ID
    $payloadData = json_decode($payload, true);
    $licenseId = $payloadData['license_id'] ?? 'UNKNOWN-' . time();

    echo "📋 License Information:\n";
    echo "   License ID: {$licenseId}\n";
    echo "   Tenant: " . ($payloadData['tenant_name'] ?? 'Unknown') . "\n";
    echo "   Edition: " . ($payloadData['edition'] ?? 'Unknown') . "\n";
    echo "   Modules: " . implode(', ', $payloadData['modules'] ?? []) . "\n";
    echo "   Expires: " . ($payloadData['expires_at'] ?? 'Unknown') . "\n\n";

    // Import using the licensing service
    $licensingService = app(\App\Services\Licensing\LicensingService::class);
    
    $success = $licensingService->importLicense(
        $licenseId,
        $payload,
        $signature,
        'imported-via-script'
    );

    if ($success) {
        echo "✅ License imported successfully!\n\n";
        
        // Verify the license
        $status = $licensingService->getStatus();
        
        echo "🔍 Verification Results:\n";
        echo "   Status: " . ($status['valid'] ? '✅ Valid' : '❌ Invalid') . "\n";
        
        if ($status['valid']) {
            echo "   Licensed Modules: " . implode(', ', $status['modules']) . "\n";
            echo "   Days Until Expiry: " . ($status['days_until_expiry'] ?? 'Unknown') . "\n";
            echo "   Max Users: " . ($status['max_users'] ?? 'Unlimited') . "\n";
        } else {
            echo "   Error: " . ($status['error'] ?? 'Unknown error') . "\n";
        }

        echo "\n🎯 License is now active! You can:\n";
        echo "1. Access licensed modules in the web interface\n";
        echo "2. Check status at: Settings → Licensing\n";
        echo "3. Test different modules based on your license\n";
        
    } else {
        echo "❌ License import failed!\n";
        echo "   Check that the signature is valid and the license is properly formatted.\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ Import failed: " . $e->getMessage() . "\n";
    exit(1);
}
