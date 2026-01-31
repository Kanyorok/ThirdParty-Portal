<?php

namespace App\Http\Middleware;

use App\Services\Licensing\LicensingService;
use Closure;
use Illuminate\Http\Request;

class RequireLicense
{
    public function handle(Request $request, Closure $next)
    {
        $lic = app(LicensingService::class)->current();
        if (! $lic->isValid()) {
            abort(402, 'License invalid or expired.');
        }

        return $next($request);
    }
}
