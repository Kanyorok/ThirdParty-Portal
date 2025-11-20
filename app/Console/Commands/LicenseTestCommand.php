<?php

namespace App\Console\Commands;

use App\Services\Licensing\LicensingService;
use Illuminate\Console\Command;

class LicenseTestCommand extends Command
{
    protected $signature = 'license:test';
    protected $description = 'Test licensing status and bypass behavior';

    public function handle(LicensingService $licensing): int
    {
        $this->info('=== License Testing ===');
        $this->newLine();

        // Display environment information
        $this->info('Environment Information:');
        $this->line('  APP_ENV: ' . app()->environment());
        $this->line('  LICENSING_BYPASS: ' . (config('licensing.bypass') ? 'true' : 'false'));
        $this->newLine();

        // Get current license
        $license = $licensing->current();

        // Display license status
        $this->info('License Status:');
        if ($license->isValid()) {
            $this->line('  <fg=green>✓ Valid</>');

            if ($license->payload) {
                $this->newLine();
                $this->info('License Details:');
                $this->line('  Tenant: ' . ($license->payload['tenant_name'] ?? 'N/A'));
                $this->line('  Edition: ' . ($license->payload['edition'] ?? 'N/A'));
                $this->line('  Max Users: ' . ($license->payload['max_users'] ?? 'N/A'));
                $this->line('  Expires: ' . ($license->expiresAt ?? 'N/A'));
                $this->line('  Allowed Modules: ' . count($license->allowedModules));
            }
        } else {
            $this->line('  <fg=red>✗ Invalid</>');
            $this->line('  Reason: ' . ($license->reason ?? 'unknown'));
        }

        $this->newLine();

        // Test module access
        $this->info('Module Access Test:');
        $testModules = [100000, 300000, 600000, 1000000, 1200000, 9800000, 9900000];
        foreach ($testModules as $moduleId) {
            $allowed = $license->allows($moduleId);
            $status = $allowed ? '<fg=green>✓ Allowed</>' : '<fg=red>✗ Denied</>';
            $this->line("  Module $moduleId: $status");
        }

        return 0;
    }
}
