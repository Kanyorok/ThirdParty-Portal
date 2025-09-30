<?php

namespace App\Console\Commands;

use App\Services\Licensing\LicensingService;
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
		if (!$result->isValid()) {
			$this->error('Invalid: '.($result->reason ?? 'unknown'));
			return self::FAILURE;
		}
		$this->info('Valid. Expires: '.$result->expiresAt);
		$this->line('Modules: '.implode(',', $result->allowedModules));
		return self::SUCCESS;
	}
}