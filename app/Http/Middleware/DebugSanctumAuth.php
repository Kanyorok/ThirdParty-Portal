<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DebugSanctumAuth
{
    public function handle(Request $request, Closure $next)
    {
        $startMem = memory_get_usage(true);
        Log::info('[DEBUG_SANCTUM] Start', [
            'path' => $request->path(),
            'memory_mb' => round($startMem / 1024 / 1024, 2)
        ]);

        try {
            $user = $request->user();
            $afterUserMem = memory_get_usage(true);
            
            Log::info('[DEBUG_SANCTUM] User loaded', [
                'has_user' => !!$user,
                'user_class' => $user ? get_class($user) : null,
                'memory_mb' => round($afterUserMem / 1024 / 1024, 2),
                'delta_mb' => round(($afterUserMem - $startMem) / 1024 / 1024, 2)
            ]);

            if ($user) {
                // Try to access the methods that might cause issues
                $isActive = method_exists($user, 'isActive') ? $user->isActive() : null;
                $afterActiveMem = memory_get_usage(true);
                
                Log::info('[DEBUG_SANCTUM] isActive checked', [
                    'is_active' => $isActive,
                    'memory_mb' => round($afterActiveMem / 1024 / 1024, 2),
                    'delta_mb' => round(($afterActiveMem - $afterUserMem) / 1024 / 1024, 2)
                ]);

                $isApproved = method_exists($user, 'isApproved') ? $user->isApproved() : null;
                $afterApprovedMem = memory_get_usage(true);
                
                Log::info('[DEBUG_SANCTUM] isApproved checked', [
                    'is_approved' => $isApproved,
                    'memory_mb' => round($afterApprovedMem / 1024 / 1024, 2),
                    'delta_mb' => round(($afterApprovedMem - $afterActiveMem) / 1024 / 1024, 2)
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('[DEBUG_SANCTUM] Exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile() . ':' . $e->getLine(),
                'memory_mb' => round(memory_get_usage(true) / 1024 / 1024, 2)
            ]);
            throw $e;
        }

        return $next($request);
    }
}
