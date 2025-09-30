<?php

namespace App\Http\Middleware;

use App\Services\Licensing\LicensingService;
use Closure;
use Illuminate\Http\Request;

class RequireModule
{
	public function handle(Request $request, Closure $next, string $moduleId)
	{
		$lic = app(LicensingService::class)->current();
		if (!$lic->isValid()) {
			abort(402, 'License invalid or expired.');
		}
		if (!$lic->allows((int)$moduleId)) {
			abort(403, 'Module not licensed.');
		}
		return $next($request);
	}
}