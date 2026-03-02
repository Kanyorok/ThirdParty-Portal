<?php

namespace App\Services\ThirdParty;

use App\Models\ThirdParty\ThirdPartyUser;

class PortalDocumentPermissionService
{
    private const SCOPES = ['prequalification', 'tender'];
    private const ACTIONS = ['view', 'upload', 'download', 'delete'];

    public function can(?ThirdPartyUser $user, string $scope, string $action): bool
    {
        if (! $user) {
            return false;
        }

        $scope = strtolower(trim($scope));
        $action = strtolower(trim($action));

        if (! in_array($scope, self::SCOPES, true) || ! in_array($action, self::ACTIONS, true)) {
            return false;
        }

        $permissions = $this->permissionsForUser($user);

        return (bool) ($permissions[$scope][$action] ?? false);
    }

    public function permissionsForUser(ThirdPartyUser $user): array
    {
        $defaults = $this->defaultPermissions($user);
        $extra = $this->normalizeExtra($user->Extra ?? null);
        $overrides = $this->normalizePermissions($extra['document_permissions'] ?? []);

        $permissions = $defaults;
        foreach (self::SCOPES as $scope) {
            foreach (self::ACTIONS as $action) {
                if (array_key_exists($action, $overrides[$scope] ?? [])) {
                    $permissions[$scope][$action] = (bool) $overrides[$scope][$action];
                }
            }
        }

        return $permissions;
    }

    public function updatePermissions(ThirdPartyUser $user, array $payload): array
    {
        $normalized = $this->normalizePermissions($payload);
        $extra = $this->normalizeExtra($user->Extra ?? null);
        $extra['document_permissions'] = $normalized;

        $user->forceFill(['Extra' => $extra])->save();

        return $this->permissionsForUser($user->fresh());
    }

    public function defaultPermissions(ThirdPartyUser $user): array
    {
        $isSupplier = $user->isSupplier();

        return [
            'prequalification' => [
                'view' => $isSupplier,
                'upload' => $isSupplier,
                'download' => $isSupplier,
                'delete' => $isSupplier,
            ],
            'tender' => [
                'view' => $isSupplier,
                'upload' => false,
                'download' => $isSupplier,
                'delete' => false,
            ],
        ];
    }

    private function normalizePermissions($payload): array
    {
        $normalized = [];
        foreach (self::SCOPES as $scope) {
            $normalized[$scope] = [];
            $scopePayload = is_array($payload) ? ($payload[$scope] ?? []) : [];
            foreach (self::ACTIONS as $action) {
                if (is_array($scopePayload) && array_key_exists($action, $scopePayload)) {
                    $normalized[$scope][$action] = (bool) $scopePayload[$action];
                }
            }
        }

        return $normalized;
    }

    private function normalizeExtra($extra): array
    {
        if (is_array($extra)) {
            return $extra;
        }

        if (is_object($extra)) {
            return (array) $extra;
        }

        if (is_string($extra)) {
            $decoded = json_decode($extra, true);
            return is_array($decoded) ? $decoded : [];
        }

        return [];
    }
}
