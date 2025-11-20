<?php

namespace App\Http\Middleware;

use App\Models\Core\Module;
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
		$id = (int) $moduleId;
		// Fast path: direct allow
		if ($lic->allows($id)) {
			return $next($request);
		}
		// Parent inheritance: if any ancestor ModuleID is licensed, allow
		try {
			$visited = [];
			$mod = Module::query()->where('ModuleID', $id)->first();
			while ($mod) {
				$mid = (int) $mod->ModuleID;
				if (in_array($mid, $lic->allowedModules, true)) {
					return $next($request);
				}
				$pid = $mod->ParentID ? (int) $mod->ParentID : null;
				if (!$pid || isset($visited[$pid])) break;
				$visited[$pid] = true;
				$mod = Module::query()->where('ModuleID', $pid)->first();
			}
		} catch (\Throwable $e) {
			// fallthrough to deny if resolution fails
		}
		abort(403, 'Module not licensed.');
		return $next($request);
	}
}
