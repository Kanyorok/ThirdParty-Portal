<?php

namespace App\Console\Commands;

use App\Services\Licensing\LicensingService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class LicenseVerifyCommand extends Command
{
    protected $signature = 'license:verify {--refresh}';
    protected $description = 'Verify the current license and display details';

    public function handle(LicensingService $licensing): int
    {
        if ($this->option('refresh')) {
            $licensing->invalidateCache();
        }
        $result = $licensing->current();
        if (! $result->isValid()) {
            $this->error('Invalid: ' . ($result->reason ?? 'unknown'));

            return self::FAILURE;
        }
        $this->info('Valid.');
        $this->line('Expires (UTC): ' . $result->expiresAt);
        $tz = config('app.timezone', 'UTC');
        $expiresLocal = CarbonImmutable::parse($result->expiresAt)->tz($tz);
        $this->line('Expires (' . $tz . '): ' . $expiresLocal->format('Y-m-d H:i:s T'));
        $this->line('Modules: ' . implode(',', $result->allowedModules));

        return self::SUCCESS;
    }
}
