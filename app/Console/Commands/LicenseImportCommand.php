<?php

namespace App\Console\Commands;

use App\Models\Licensing\LicenseRecord;
use App\Services\Licensing\LicensingService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LicenseImportCommand extends Command
{
	protected $signature = 'license:import {payload} {signature} {--id=} {--kid=}';
	protected $description = 'Import a license using payload and signature file paths or strings';

	public function handle(LicensingService $licensing): int
	{
		$payloadArg = $this->argument('payload');
		$signatureArg = $this->argument('signature');

		$payload = File::exists($payloadArg) ? File::get($payloadArg) : $payloadArg;
		$signature = File::exists($signatureArg) ? trim(File::get($signatureArg)) : $signatureArg;
		$licenseId = $this->option('id') ?: 'CLI-'.now('UTC')->format('Ymd-His');
		$kid = $this->option('kid') ?: config('licensing.public_key_id');

		LicenseRecord::create([
			'LicenseId' => $licenseId,
			'PayloadJson' => $payload,
			'SignatureBase64' => $signature,
			'PublicKeyId' => $kid,
			'Status' => 1,
			'CreatedOn' => now('UTC')->toDateTimeString(),
		]);

		$licensing->invalidateCache();
		$result = $licensing->current();
		if (!$result->isValid()) {
			$this->error('License invalid: '.($result->reason ?? 'unknown'));
			return self::FAILURE;
		}
		$this->info('License imported and validated. Expires: '.$result->expiresAt);
		return self::SUCCESS;
	}
}