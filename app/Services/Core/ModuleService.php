<?php

namespace App\Services\Core;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Models\Auth\User;
use App\Models\Core\Module;
use App\Services\Licensing\LicensingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth as AuthFacade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ModuleService
{
    /**
     * In-request caches to avoid repeated DB/route scans.
     */
    private static array $permissionSetCache = [];
    private static array $normalizedPermissionSetCache = [];
    private static ?array $routePermissionMap = null;

    public static function generateNavbar(User $user = null): string
    {
        if (config('app.env') === 'local') {
            return self::buildNavbar();
            // return self::getNavbar();
        }

        return Cache::remember(self::getNavbarCacheKey($user), 3600, static function () {
            return self::buildNavbar();
            // return self::getNavbar();
        });
    }

    public static function getNavbarCacheKey(User $user = null): string
    {
        if (!$user) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }
        if (!$user) {
            return 'guest-navbar-modules';
        }
        return $user->UserID . '-navbar_modules';
    }

    public static function clearNavbarCache(User $user = null): bool
    {
        return Cache::forget(self::getNavbarCacheKey($user));
    }

    protected static function buildNavbar(): string
    {
        $menu = '';
        foreach (self::getNavbar() as $module) {
            $menu .= '<li class="pc-item';
            if (!empty($module['children'])) {
                if (request()->is(Str::of($module['name'])->ucfirst()->lower()->toString() . '*')) {
                    $menu .= ' active pc-trigger';
                }
                $menu .= ' pc-hasmenu';
            } elseif (is_string($module['route']) && request()->route()?->named($module['route'])) {
                $menu .= ' active';
            }
            $path = parse_url($module['route'], PHP_URL_PATH) ?? '/';
            $menu .= '" data-item-id="' . $module['id'] . '">';
            $menu .= '<a href="' . $module['route'] . '" class="pc-link" data-ajax="1" data-route="' . $path . '" data-route-id="' . $path . '">';
            $menu .= '<span class="pc-micon">';
            $menu .= $module['icon'] ?? '<i data-feather="box"></i>';
            $menu .= '</span>';
            $menu .= '<span class="pc-mtext">' . $module['name'] . '</span>';
            if (!empty($module['children'])) {
                $menu .= '<span class="pc-arrow"><i data-feather="chevron-right"></i></span>';
            }
            $menu .= '</a>';
            if (!empty($module['children'])) {
                $menu .= self::_buildSubNavbar($module['children'], $module['id']);
            }
            $menu .= ' </li>';
        }


        return $menu;
    }

    protected static function _buildSubNavbar(array $children, $parentId = null): string
    {
        $menu = '<ul class="pc-submenu">';
        foreach ($children as $child) {
            $menu .= '<li class="pc-item';
            if (!empty($child['children'])) {
                $menu .= ' pc-hasmenu';
            } elseif (is_string($child['route']) && request()->route()?->named($child['route'])) {
                $menu .= ' active';
            }
            $childPath = parse_url($child['route'], PHP_URL_PATH) ?? '/';
            $menu .= '" data-item-id="' . $child['id'] . '"' . ($parentId ? ' data-parent-id="' . $parentId . '"' : '') . '>';
            $menu .= '<a href="' . $child['route'] . '" class="pc-link" data-ajax="1" data-route="' . $childPath . '" data-route-id="' . $childPath . '">';
            $menu .= '<span>' . $child['name'] . '</span>';
            if (!empty($child['children'])) {
                $menu .= '<span class="pc-arrow"><i data-feather="chevron-right"></i></span>';
            }
            $menu .= '</a>';

            if (!empty($child['children'])) {
                $menu .= self::_buildSubNavbar($child['children'], $child['id']);
            }
            $menu .= '</li>';
        }
        $menu .= ' </ul>';

        return $menu;
    }


    private static function getNavbar(): Collection
    {
        $modules = Module::all();

        // Filter top-level modules by license (parent-only license; children inherit)
        $license = app(LicensingService::class)->current();
        $allowedIds = $license->isValid() ? $license->allowedModules : [];

        $topLevelModules = $modules
            ->whereNull('ParentID')
            ->sortBy('ModuleID')
            ->filter(static function ($m) use ($allowedIds, $modules) {
                // Include if top-level ModuleID is licensed, or any ancestor (N/A for top-level)
                return in_array((int)$m->ModuleID, $allowedIds, true);
            });

        $navbar = collect();

        foreach ($topLevelModules as $module) {
            $navbar->push(self::buildNavbarItem($module, $modules));
        }

        // Filter by permissions for current user
        $user = AuthFacade::user();
        if ($user instanceof User) {
            // Admin bypass: show all modules/submodules without filtering
            if (self::isSuper($user)) {
                return $navbar; // return full, unfiltered menu
            }
            $filtered = [];
            $permSet = self::getUserPermissionSet($user);
            self::buildRoutePermissionMapOnce();
            foreach ($navbar as $item) {
                $filteredItem = self::filterItemForUser($item, $user, $permSet, parentName: null);
                if ($filteredItem !== null) {
                    $filtered[] = $filteredItem;
                }
            }
            return collect($filtered);
        }

        return $navbar;
    }

    /**
     * Recursively build navbar items
     *
     * @param Module $module
     * @param Collection $allModules
     * @return array
     */
    private static function buildNavbarItem(Module $module, Collection $allModules): array
    {
        $routeUrl = 'javascript:void(0)';
        if (is_string($module->Route) && Route::has($module->Route)) {
            // Safely resolve route URL only if it has no required parameters
            try {
                $named = Route::getRoutes()->getByName($module->Route);
                if ($named) {
                    $uri = method_exists($named, 'uri') ? $named->uri() : '';
                    // Detect required parameters like {param} (without ?)
                    $hasRequiredParams = is_string($uri) && preg_match('/\{[^}\?]+\}/', $uri);
                    if (!$hasRequiredParams) {
                        $routeUrl = route($module->Route);
                    }
                }
            } catch (\Throwable $e) {
                $routeUrl = 'javascript:void(0)';
            }
        }

        $item = [
            'id' => $module->ModuleID,
            'name' => $module->Name,
            'icon' => $module->Icon ?? 'fa fa-circle-o',
            'route' => $routeUrl,
            'route_name' => is_string($module->Route) ? $module->Route : null,
            'description' => $module->Description ?? '',
            'children' => []
        ];

        // Get children of this module
        $children = $allModules->where('ParentID', $module->ModuleID)->sortBy('ModuleID');

        if ($children->isNotEmpty()) {
            foreach ($children as $child) {
                $item['children'][] = self::buildNavbarItem($child, $allModules);
            }
        }

        return $item;
    }

    /**
     * Recursively filter a navbar item for a user; returns null if user cannot access any part of it.
     */
    private static function filterItemForUser(array $item, User $user, array $permSet, ?string $parentName): ?array
    {
        // Preserve original children for fallback logic
        $originalChildren = $item['children'] ?? [];
            // Filter children by resolver (read visibility)
            $filteredChildren = [];
            foreach ($originalChildren as $child) {
                $filteredChild = self::filterItemForUser($child, $user, $permSet, parentName: ($item['name'] ?? null));
                if ($filteredChild !== null) {
                    $filteredChildren[] = $filteredChild;
                }
            }
            $item['children'] = $filteredChildren;

        // If this item has a route, check if user can access it
        $routeName = $item['route_name'] ?? null;
        // Submodule key inference: prefer route base or menu item name kebab
        $subKey = null;
        if (is_string($routeName) && $routeName !== '') {
            $parts = explode('.', Str::lower($routeName));
            $actions = ['index','create','store','edit','update','destroy','show','list','data'];
            $candidate = end($parts) ?: null;
            if ($candidate && in_array($candidate, $actions, true) && count($parts) > 1) {
                $candidate = prev($parts) ?: $candidate;
            }
            $subKey = $candidate ?: ($parts[0] ?? null);
        }
        if (!$subKey) {
            $subKey = Str::kebab(Str::lower((string)($item['name'] ?? '')));
        }
        // Visibility rule: submodule is visible only if user has read on that submodule (or it is open)
        $canAccessRoute = $subKey ? PermissionResolver::isReadable($user, $subKey) : false;

        // Strict rule: keep item only if the route is accessible OR it has accessible children
        if ($canAccessRoute || !empty($filteredChildren)) {
            return $item;
        }

    // No fallback: parent is visible only if it has any readable children

        return null;
    }

    /**
     * Permissive filter: include an item if user can access its route via ANY action (read/write/update/delete/approval),
     * or if any of its children are included by the same permissive rules.
     */
    private static function filterItemForUserAny(array $item, User $user, array $permSet): ?array
    {
        $children = $item['children'] ?? [];
        $filteredChildren = [];
        foreach ($children as $child) {
            $fc = self::filterItemForUserAny($child, $user, $permSet);
            if ($fc !== null) {
                $filteredChildren[] = $fc;
            }
        }

        $item['children'] = $filteredChildren;

        $routeName = $item['route_name'] ?? null;
        $canAccessAny = $routeName ? self::userCanAccessRoute($user, $permSet, $routeName) : false;
        if ($canAccessAny || !empty($filteredChildren)) {
            return $item;
        }
        return null;
    }

    /**
     * Strict read-only route access used for menu visibility: require read/view for route base
     */
    private static function userCanReadRoute(User $user, array $permSet, string $routeName): bool
    {
        // Overrides for departmental plan routes -> departmentneeds-read
        $overrides = [
            'procurementdepartmentalplan.index' => PermissionEnum::DepartmentNeedsRead->value,
            'procurementdepartmentalplan.view' => PermissionEnum::DepartmentNeedsRead->value,
            'procurementdepartmentalplan.data' => PermissionEnum::DepartmentNeedsRead->value,
            // approvals list is not a read of base module; keep it separate
        ];
        if (isset($overrides[$routeName])) {
            $required = Str::lower($overrides[$routeName]);
            return isset($permSet[$required]);
        }

        // Prefer explicit map from permission middleware
        $required = self::$routePermissionMap[$routeName] ?? null;
        if (is_string($required) && $required !== '') {
            // Honor explicit permission middleware: if user has it, consider this route visible in the menu
            if (isset($permSet[$required])) return true;
            // Also accept common read/view suffixes if present
            if (Str::endsWith($required, ['-read','-view']) && isset($permSet[$required])) return true;
        }

        // Heuristic: try multiple base candidates from dotted route names (e.g., settings.users.index → users)
        $segments = array_values(array_filter(explode('.', Str::lower($routeName))));
        $actions = ['index','create','store','edit','update','destroy','show','list','data'];
        $candidates = [];
        // Prefer segment after 'settings' if present
        $idx = array_search('settings', $segments, true);
        if ($idx !== false && isset($segments[$idx + 1])) {
            $candidates[] = $segments[$idx + 1];
        }
        // Last non-action segment
        for ($i = count($segments) - 1; $i >= 0; $i--) {
            if (!in_array($segments[$i], $actions, true)) {
                $candidates[] = $segments[$i];
                break;
            }
        }
        // Add all non-action segments as fallbacks
        foreach ($segments as $seg) {
            if (!in_array($seg, $actions, true)) $candidates[] = $seg;
        }
        $candidates = array_values(array_unique($candidates));

        foreach ($candidates as $base) {
            $baseCompressed = preg_replace('/[^a-z0-9]/', '', $base);
            foreach (['read','view'] as $suf) {
                $p1 = $base . '-' . $suf;
                if (isset($permSet[$p1])) return true;
                $p2 = $baseCompressed . '-' . $suf;
                if (isset($permSet[$p2])) return true;
            }
            // Accept bare permission names (e.g., 'roles', 'users', 'teams', 'branches') commonly used under Settings
            if (isset($permSet[$base]) || isset($permSet[$baseCompressed])) return true;
        }
        return false;
    }

    /**
     * Check if a user has any permission belonging to a module name (best-effort mapping via ModulesEnum description).
     */
    private static function userHasAnyPermissionInModuleName(User $user, array $permSet, string $moduleName): bool
    {
        $moduleName = Str::of($moduleName)->lower()->toString();
        // Map name to ModulesEnum by description match
        $target = collect(ModulesEnum::cases())->first(function ($m) use ($moduleName) {
            $desc = Str::of($m->description())->lower()->toString();
            return Str::contains($desc, $moduleName) || Str::contains($moduleName, Str::lower($desc));
        });

        if (!$target instanceof ModulesEnum) {
            // Fall back: weak heuristics by common names
            $map = [
                'procurement' => ModulesEnum::Procurement,
                'inventory' => ModulesEnum::Inventory,
                'property' => ModulesEnum::Property,
                'fleet' => ModulesEnum::Fleet,
                'dms' => ModulesEnum::DMS,
                'legal' => ModulesEnum::Legal,
                'insurance' => ModulesEnum::Insurance,
                'hrm' => ModulesEnum::HRM,
                'finance' => ModulesEnum::Finance,
                'settings' => ModulesEnum::Settings,
                'budget' => ModulesEnum::BudgetLine,
                'supplier' => ModulesEnum::ThirdParty,
                'suppliers' => ModulesEnum::ThirdParty,
                'third party' => ModulesEnum::ThirdParty,
            ];
            foreach ($map as $key => $enum) {
                if (Str::contains($moduleName, $key)) {
                    $target = $enum; break;
                }
            }
        }

        if (!$target instanceof ModulesEnum) {
            return false;
        }

        // Check if user has any permission from PermissionEnum within this module
        foreach (PermissionEnum::cases() as $perm) {
            /** @var PermissionEnum $perm */
            if ($perm->module() === $target) {
                if (isset($permSet[$perm->value])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Determine if user can access a route via explicit 'permission:xxx' middleware or heuristic mapping.
     */
    private static function userCanAccessRoute(User $user, array $permSet, string $routeName): bool
    {
        // Manual overrides for routes that don't follow a clean naming → permission pattern
        // Department Needs
        $overrides = [
            'procurementdepartmentalplan.index' => PermissionEnum::DepartmentNeedsRead->value,
            'procurementdepartmentalplan.view' => PermissionEnum::DepartmentNeedsRead->value,
            'procurementdepartmentalplan.data' => PermissionEnum::DepartmentNeedsRead->value,
            'procurementdepartmentalplan.create' => PermissionEnum::DepartmentNeedsWrite->value,
            'procurementdepartmentalplan.store' => PermissionEnum::DepartmentNeedsWrite->value,
            'procurementdepartmentalplan.updateLine' => PermissionEnum::DepartmentNeedsUpdate->value,
            'procurementdepartmentalplan.destroy' => PermissionEnum::DepartmentNeedsDelete->value,
            // Approvals
            'department-need-approval.index' => PermissionEnum::DepartmentNeedsApproval->value,
            'department-need-approval.show' => PermissionEnum::DepartmentNeedsApproval->value,
            'department-need-approval.update' => PermissionEnum::DepartmentNeedsApproval->value,
            'department-need-approval.destroy' => PermissionEnum::DepartmentNeedsApproval->value,
        ];
        if (isset($overrides[$routeName])) {
            $required = Str::lower($overrides[$routeName]);
            return isset($permSet[$required]);
        }

        // Use prebuilt route->permission map if available
        $required = self::$routePermissionMap[$routeName] ?? null;
        if (is_string($required) && $required !== '') {
            return isset($permSet[$required]);
        }

        // Heuristic fallback from route base name
        // Prefer the first segment after module prefix when present (e.g., rfqs.index → rfq; tendersubmission.index → tendersubmission)
        $base = Str::of($routeName)->before('.')->lower()->toString();
        // Generate candidate bases: keep separators and compressed
        $baseCompressed = preg_replace('/[^a-z0-9]/', '', $base);
        $suffixes = ['read','view','create','write','update','edit','delete','destroy','approval','approve'];
        foreach ($suffixes as $suf) {
            // raw style: base(with hyphens/segments)-suffix
            $p1 = $base . '-' . $suf;
            if (isset($permSet[$p1])) return true;
            // compressed base-suffix (covers camelCase bases stored without separators)
            $p2 = $baseCompressed . '-' . $suf;
            if (isset($permSet[$p2])) return true;
        }
        return false;
    }

    /**
     * Build a route name -> permission mapping once per request by scanning route middleware.
     */
    private static function buildRoutePermissionMapOnce(): void
    {
        if (is_array(self::$routePermissionMap)) return;
        $map = [];
        try {
            foreach (Route::getRoutes() as $route) {
                $name = $route->getName();
                if (!$name) continue;
                $middlewares = method_exists($route, 'gatherMiddleware') ? $route->gatherMiddleware() : ($route->middleware() ?? []);
                foreach ($middlewares as $mw) {
                    if (is_string($mw) && Str::startsWith($mw, 'permission:')) {
                        $map[$name] = Str::lower((string)Str::after($mw, 'permission:'));
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
        }
        self::$routePermissionMap = $map;
    }

    /**
     * Get a set of permission names for the current user/branch for this request.
     */
    public static function getUserPermissionSet(User $user): array
    {
        $branchId = (string) (session('LoginBranchId') ?? 'no-branch');
        $key = $user->getAuthIdentifier() . '|' . $branchId;
        if (array_key_exists($key, self::$permissionSetCache)) {
            return self::$permissionSetCache[$key];
        }
        try {
            $perms = $user->getPermissionsViaRoles()->pluck('name')->filter()->values()->all();
        } catch (\Throwable $e) {
            $perms = [];
        }
        // Convert to lowercased set for O(1) lookups
        $set = [];
        foreach ($perms as $p) {
            $set[Str::lower((string)$p)] = true;
        }
        // Warm normalized cache sibling
        self::$normalizedPermissionSetCache[$key] = self::normalizePermissionSetKeys(array_keys($set));
        return self::$permissionSetCache[$key] = $set;
    }

    private static function isSuper(User $user): bool
    {
        return $user->hasRole(['admin', 'Admin', 'super-admin', 'Super Admin']);
    }

    /**
     * Determine if the user has a base-action permission (e.g., tender-read) with robust normalization.
     */
    public static function userHasAction(User $user, string $base, string $action): bool
    {
        $raw = self::getUserPermissionSet($user); // lowercased keys
        $norm = self::getNormalizedPermissionSet($user);

        $baseLower = Str::lower($base);
        $baseCompressed = preg_replace('/[^a-z0-9]/', '', $baseLower);
        $baseKebab = Str::kebab($baseLower);

        $synonyms = [
            'read' => ['read','view'],
            'create' => ['create','write'],
            'write' => ['create','write'],
            'update' => ['update','edit'],
            'delete' => ['delete','destroy'],
            'approval' => ['approval','approve'],
            'approve' => ['approval','approve'],
        ];
        $suffixes = $synonyms[$action] ?? [$action];

        foreach ($suffixes as $suf) {
            // raw variants
            $c1 = $baseLower . '-' . $suf; // e.g., tender-read, tenderinvitation-read
            if (isset($raw[$c1])) return true;
            $c2 = $baseCompressed . '-' . $suf; // e.g., masterlist-view
            if (isset($raw[$c2])) return true;
            $c3 = $baseKebab . '-' . $suf; // e.g., master-list-view
            if (isset($raw[$c3])) return true;

            // normalized compressed (no separators)
            $n1 = $baseCompressed . $suf; // e.g., masterlistview
            if (isset($norm[$n1])) return true;
        }
        return false;
    }

    private static function getNormalizedPermissionSet(User $user): array
    {
        $branchId = (string) (session('LoginBranchId') ?? 'no-branch');
        $key = $user->getAuthIdentifier() . '|' . $branchId;
        if (isset(self::$normalizedPermissionSetCache[$key])) {
            return self::$normalizedPermissionSetCache[$key];
        }
        // Ensure raw cache is warmed
        $raw = self::getUserPermissionSet($user);
        return self::$normalizedPermissionSetCache[$key] = self::normalizePermissionSetKeys(array_keys($raw));
    }

    private static function normalizePermissionSetKeys(array $names): array
    {
        $set = [];
        foreach ($names as $name) {
            $norm = preg_replace('/[^a-z0-9]/', '', Str::lower((string)$name));
            $set[$norm] = true;
        }
        return $set;
    }
}
