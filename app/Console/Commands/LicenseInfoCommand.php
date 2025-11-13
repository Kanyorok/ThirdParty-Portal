<?php

namespace App\Console\Commands;

use App\Models\Licensing\LicenseRecord;
use Illuminate\Console\Command;

class LicenseInfoCommand extends Command
{
	protected $signature = 'license:info';
	protected $description = 'Show latest license record with payload';

	public function handle(): int
	{
		$rec = LicenseRecord::query()->orderByDesc('Id')->first();
		if (!$rec) {
			$this->warn('No license records');
			return self::SUCCESS;
		}
		$this->line('LicenseId: '.$rec->LicenseId);
		$this->line('PublicKeyId: '.$rec->PublicKeyId);
		$this->line('Status: '.$rec->Status);
		$this->line('CreatedOn: '.$rec->CreatedOn);
		$this->newLine();
		$this->line('Payload:');
		$this->line($rec->PayloadJson);
		return self::SUCCESS;
	}
}