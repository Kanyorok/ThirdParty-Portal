<?php
/**
 * License Management Script
 * 
 * View, remove, and manage existing licenses
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Load Laravel app
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->boot();

echo "🛠️  License Management Tool\n";
echo str_repeat("=", 60) . "\n\n";

// Show available commands
if (count($argv) < 2) {
    echo "Available commands:\n\n";
    echo "📋 List all licenses:\n";
    echo "   php scripts/manage_licenses.php list\n\n";
    echo "🗑️  Remove a license:\n";
    echo "   php scripts/manage_licenses.php remove <license_id>\n\n";
    echo "🧹 Remove all licenses:\n";
    echo "   php scripts/manage_licenses.php clear\n\n";
    echo "📊 Show current status:\n";
    echo "   php scripts/manage_licenses.php status\n\n";
    echo "🔄 Refresh license cache:\n";
    echo "   php scripts/manage_licenses.php refresh\n\n";
    exit(1);
}

$command = $argv[1];
$licensingService = app(\App\Services\Licensing\LicensingService::class);

switch ($command) {
    case 'list':
        echo "📋 All Licenses:\n";
        echo str_repeat("-", 40) . "\n";
        
        $licenses = \App\Models\Licensing\License::orderBy('CreatedOn', 'desc')->get();
        
        if ($licenses->isEmpty()) {
            echo "   No licenses found.\n";
        } else {
            foreach ($licenses as $license) {
                $payload = $license->parsed_payload;
                $status = $license->Status == 1 ? '🟢 Active' : '🔴 Revoked';
                $expired = $license->is_expired ? '⏰ Expired' : '✅ Valid';
                
                echo "   {$license->LicenseId} | {$status} | {$expired}\n";
                echo "     Tenant: " . ($payload['tenant_name'] ?? 'Unknown') . "\n";
                echo "     Edition: " . ($payload['edition'] ?? 'Unknown') . "\n";
                echo "     Modules: " . implode(', ', $payload['modules'] ?? []) . "\n";
                echo "     Created: " . $license->CreatedOn->format('Y-m-d H:i:s') . "\n";
                echo "     Expires: " . ($payload['expires_at'] ? date('Y-m-d H:i:s', strtotime($payload['expires_at'])) : 'Never') . "\n\n";
            }
        }
        break;

    case 'remove':
        if (count($argv) < 3) {
            echo "❌ Error: License ID required\n";
            echo "Usage: php scripts/manage_licenses.php remove <license_id>\n";
            exit(1);
        }
        
        $licenseId = $argv[2];
        $license = \App\Models\Licensing\License::where('LicenseId', $licenseId)->first();
        
        if (!$license) {
            echo "❌ License '{$licenseId}' not found\n";
            exit(1);
        }
        
        $license->update(['Status' => 0]);
        \App\Models\Licensing\LicenseAudit::logEvent(
            \App\Models\Licensing\LicenseAudit::EVENT_LICENSE_REVOKED,
            'License revoked via management script',
            $licenseId
        );
        
        $licensingService->invalidateCache();
        
        echo "✅ License '{$licenseId}' has been revoked\n";
        break;

    case 'clear':
        echo "⚠️  This will revoke ALL licenses. Are you sure? (yes/no): ";
        $confirmation = trim(fgets(STDIN));
        
        if (strtolower($confirmation) === 'yes') {
            $count = \App\Models\Licensing\License::where('Status', 1)->count();
            \App\Models\Licensing\License::where('Status', 1)->update(['Status' => 0]);
            
            \App\Models\Licensing\LicenseAudit::logEvent(
                \App\Models\Licensing\LicenseAudit::EVENT_LICENSE_REVOKED,
                "All licenses ({$count}) revoked via management script"
            );
            
            $licensingService->invalidateCache();
            
            echo "✅ All {$count} licenses have been revoked\n";
        } else {
            echo "❌ Operation cancelled\n";
        }
        break;

    case 'status':
        echo "📊 Current License Status:\n";
        echo str_repeat("-", 40) . "\n";
        
        $status = $licensingService->getStatus();
        
        if ($status['valid']) {
            echo "   Status: ✅ Licensed\n";
            echo "   Tenant: " . $status['tenant_name'] . "\n";
            echo "   Edition: " . $status['edition'] . "\n";
            echo "   Modules: " . implode(', ', $status['modules']) . "\n";
            echo "   Max Users: " . $status['max_users'] . "\n";
            echo "   Days Until Expiry: " . ($status['days_until_expiry'] ?? 'Unknown') . "\n";
            
            if ($status['in_grace_period']) {
                echo "   ⚠️  Grace Period: License expired but still functional\n";
            }
            
            if (!empty($status['features'])) {
                echo "   Features: " . implode(', ', array_keys(array_filter($status['features']))) . "\n";
            }
            
        } else {
            echo "   Status: ❌ Invalid/Unlicensed\n";
            echo "   Error: " . ($status['error'] ?? 'Unknown') . "\n";
        }
        
        // Instance info
        $instance = \App\Models\Licensing\Instance::current();
        echo "\n📱 Instance Information:\n";
        echo "   DB GUID: " . $instance->DbGuid . "\n";
        echo "   Host Fingerprint: " . substr($instance->HostFingerprint, 0, 32) . "...\n";
        break;

    case 'refresh':
        echo "🔄 Refreshing license cache...\n";
        $licensingService->invalidateCache();
        
        // Force re-validation
        $result = $licensingService->verifyAndLoad();
        
        echo "✅ Cache refreshed\n";
        echo "   New Status: " . ($result->isValid() ? 'Valid' : 'Invalid') . "\n";
        
        if (!$result->isValid()) {
            echo "   Error: " . $result->getError() . "\n";
        }
        break;

    default:
        echo "❌ Unknown command: {$command}\n";
        echo "Run without arguments to see available commands.\n";
        exit(1);
}
