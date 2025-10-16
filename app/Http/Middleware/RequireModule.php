<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\Licensing\LicensingService;
use App\Models\Licensing\LicenseAudit;
use Illuminate\Support\Facades\Log;

class RequireModule
{
    private LicensingService $licensingService;

    public function __construct(LicensingService $licensingService)
    {
        $this->licensingService = $licensingService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $moduleKey): Response
    {
        try {
            $license = $this->licensingService->current();
            
            if (!$license->isValid()) {
                $this->handleLicenseError($request, $license->getError(), $moduleKey);
                return $this->denyAccess($request, 'Invalid or expired license');
            }

            // Check if the requested module key is licensed
            // Since we only license parent modules, this check is direct
            if (!$license->allows($moduleKey)) {
                LicenseAudit::logModuleDenied($moduleKey);
                
                Log::warning('Parent module access denied', [
                    'requested_module' => $moduleKey,
                    'user_id' => auth()->id(),
                    'route' => $request->route()?->getName(),
                    'licensed_modules' => $license->getAllowedModules()
                ]);

                return $this->denyAccess($request, "Module '{$moduleKey}' is not licensed");
            }

            // Add license info to request for potential use in controllers
            $request->merge([
                'license_result' => $license,
                'licensed_modules' => $license->getAllowedModules(),
                'current_module' => $moduleKey
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Module middleware error', [
                'module' => $moduleKey,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->denyAccess($request, 'License verification failed');
        }
    }

    /**
     * Handle various license errors
     */
    private function handleLicenseError(Request $request, string $error, string $moduleKey): void
    {
        $errorMessages = [
            'no_license' => 'No license found',
            'bad_signature' => 'Invalid license signature',
            'expired' => 'License has expired',
            'wrong_instance' => 'License not valid for this instance',
            'replay_or_downgrade' => 'License replay attempt detected',
            'verification_error' => 'License verification failed'
        ];

        $message = $errorMessages[$error] ?? 'Unknown license error';
        
        Log::warning('License validation failed in middleware', [
            'error' => $error,
            'message' => $message,
            'module' => $moduleKey,
            'user_id' => auth()->id(),
            'route' => $request->route()?->getName()
        ]);
    }

    /**
     * Return access denied response
     */
    private function denyAccess(Request $request, string $message): Response
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Access Denied',
                'message' => $message,
                'code' => 403
            ], 403);
        }

        // For web requests, redirect to a licensing error page or show error
        return response()->view('errors.licensing', [
            'message' => $message,
            'title' => 'Module Not Licensed'
        ], 403);
    }
}
