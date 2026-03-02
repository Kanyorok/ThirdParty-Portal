<?php

namespace App\Http\Middleware;

use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\ThirdParty\PortalDocumentPermissionService;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalDocumentPermission
{
    public function __construct(
        private readonly PortalDocumentPermissionService $permissionService
    ) {
    }

    public function handle(Request $request, Closure $next, string $scope, string $action = 'view'): Response
    {
        $user = $request->user();
        if (! $user instanceof ThirdPartyUser) {
            return $this->deny('Unauthenticated.', 401);
        }

        if (! $this->permissionService->can($user, $scope, $action)) {
            return $this->deny(sprintf(
                'You do not have permission to %s %s documents.',
                strtolower($action),
                strtolower($scope)
            ));
        }

        return $next($request);
    }

    private function deny(string $message, int $status = 403): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $status === 401 ? 'UNAUTHENTICATED' : 'FORBIDDEN_DOCUMENT_PERMISSION',
        ], $status);
    }
}
