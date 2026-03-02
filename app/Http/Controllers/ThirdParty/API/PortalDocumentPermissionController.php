<?php

namespace App\Http\Controllers\ThirdParty\API;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdPartyUser;
use App\Services\ThirdParty\PortalDocumentPermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PortalDocumentPermissionController extends Controller
{
    public function __construct(
        private readonly PortalDocumentPermissionService $permissionService
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (! $user) {
            return $this->unauthenticated();
        }

        return response()->json([
            'success' => true,
            'permissions' => $this->permissionService->permissionsForUser($user),
            'defaults' => $this->permissionService->defaultPermissions($user),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (! $user) {
            return $this->unauthenticated();
        }

        $validated = $request->validate([
            'permissions' => 'required|array',
            'permissions.prequalification' => 'nullable|array',
            'permissions.prequalification.view' => 'nullable|boolean',
            'permissions.prequalification.upload' => 'nullable|boolean',
            'permissions.prequalification.download' => 'nullable|boolean',
            'permissions.prequalification.delete' => 'nullable|boolean',
            'permissions.tender' => 'nullable|array',
            'permissions.tender.view' => 'nullable|boolean',
            'permissions.tender.upload' => 'nullable|boolean',
            'permissions.tender.download' => 'nullable|boolean',
            'permissions.tender.delete' => 'nullable|boolean',
        ]);

        $permissions = $this->permissionService->updatePermissions($user, $validated['permissions']);

        return response()->json([
            'success' => true,
            'message' => 'Document permissions updated successfully.',
            'permissions' => $permissions,
        ]);
    }

    private function resolveUser(Request $request): ?ThirdPartyUser
    {
        $user = $request->user();
        return $user instanceof ThirdPartyUser ? $user : null;
    }

    private function unauthenticated(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => __('auth.unauthenticated'),
        ], 401);
    }
}
