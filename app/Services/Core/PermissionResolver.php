<?php

namespace App\Services\Core;

use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionResolver
{
    /**
     * Build map: [submodule_key => ['read'=>bool,'write'=>bool,'update'=>bool,'delete'=>bool,'approve'=>bool]]
     * Rules:
     * - Normalize permission names case-insensitively
     * - Expect names as `submodule.action` or `<compound>-<action>` from PermissionEnum; resolve to submodule + action
     * - If submodule has no defined permissions in DB (t_Permissions), treat as open (all true)
     * - Union across user roles
     */
    public static function forUser(User $user): array
    {
        $cacheKey = 'permres:' . $user->getAuthIdentifier() . ':' . (string)(session('LoginBranchId') ?? 'no-branch');
        // Per-request in-memory cache
        static $reqCache = [];
        if (isset($reqCache[$cacheKey])) return $reqCache[$cacheKey];

        // Load all submodule keys from DB (t_SubModules.Key or Name)
        $submodules = collect();
        try {
            $submodules = collect(DB::table('t_SubModules')->select('Key')->pluck('Key'))->filter()->values();
        } catch (\Throwable $e) {
            // fallback: empty, resolver still works with discovered perms
            $submodules = collect();
        }
        $subKeys = $submodules->map(fn($k) => Str::lower(trim((string)$k)))->filter()->unique()->values()->all();

        // Load user permissions (Spatie) once
        try {
            $userPerms = $user->getPermissionsViaRoles()->pluck('name')->all();
        } catch (\Throwable $e) {
            $userPerms = [];
        }

        // Normalize to submodule.action
        $matrix = [];
        foreach ($userPerms as $name) {
            $name = Str::lower(trim((string)$name));
            if ($name === '') continue;
            [$sub, $act] = self::splitPermission($name);
            if (!$sub || !$act) continue;
            $matrix[$sub] = $matrix[$sub] ?? self::emptyRow();
            if (isset($matrix[$sub][$act])) $matrix[$sub][$act] = true;
        }

        // Determine which submodules are configured (protected) vs open
        $configured = self::configuredSubmodules(); // set of submodule keys with any defined perms
        // Initialize open submodules (all true)
        foreach ($subKeys as $sk) {
            if (!isset($configured[$sk])) {
                $matrix[$sk] = self::allTrueRow();
            }
        }

        // Also, if a submodule appears in user's perms but not in configured, keep its row as-is (already set from perms)
        return $reqCache[$cacheKey] = $matrix;
    }

    public static function can(User $user, string $submodule, string $action): bool
    {
        $sub = Str::lower(trim($submodule));
        $act = self::normalizeAction($action);
        $map = self::forUser($user);
        // If not present in map, check open-by-absence
        $configured = self::configuredSubmodules();
        if (!isset($configured[$sub])) {
            return true; // open
        }
        return (bool)($map[$sub][$act] ?? false);
    }

    public static function isReadable(User $user, string $submodule): bool
    {
        return self::can($user, $submodule, 'read');
    }

    private static function splitPermission(string $name): array
    {
        // Prefer dot format: submodule.action
        if (Str::contains($name, '.')) {
            [$sub, $act] = explode('.', $name, 2);
            return [trim($sub), self::normalizeAction($act)];
        }
        // Support legacy hyphen style from PermissionEnum: base-action (e.g., role-create, users-read)
        $actionMap = ['read','view'=>'read','create'=>'write','write'=>'write','update'=>'update','edit'=>'update','delete'=>'delete','destroy'=>'delete','approval'=>'approve','approve'=>'approve'];
        $lastDash = strrpos($name, '-');
        if ($lastDash !== false) {
            $sub = substr($name, 0, $lastDash);
            $act = substr($name, $lastDash + 1);
            $act = $actionMap[$act] ?? $act;
            return [trim($sub), self::normalizeAction($act)];
        }
        // Bare name implies full access (rare): treat as read for visibility
        return [$name, 'read'];
    }

    private static function normalizeAction(string $act): string
    {
        $act = Str::lower(trim($act));
        return match ($act) {
            'view' => 'read',
            'create','write' => 'write',
            'edit','update' => 'update',
            'destroy','delete' => 'delete',
            'approval','approve' => 'approve',
            default => $act,
        };
    }

    private static function configuredSubmodules(): array
    {
        static $conf;
        if (is_array($conf)) return $conf;
        $set = [];
        try {
            $pairs = DB::table('t_Permissions')->select('SubModuleKey')->pluck('SubModuleKey');
            foreach ($pairs as $k) {
                $k = Str::lower(trim((string)$k));
                if ($k !== '') $set[$k] = true;
            }
        } catch (\Throwable $e) {
            $set = [];
        }
        return $conf = $set;
    }

    private static function emptyRow(): array
    {
        return ['read'=>false,'write'=>false,'update'=>false,'delete'=>false,'approve'=>false];
    }

    private static function allTrueRow(): array
    {
        return ['read'=>true,'write'=>true,'update'=>true,'delete'=>true,'approve'=>true];
    }
}
