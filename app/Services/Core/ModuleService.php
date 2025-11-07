<?php

namespace App\Services\Core;

use App\Models\Auth\User;
use App\Models\Core\Module;
use App\Services\Licensing\LicensingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class ModuleService
{

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
        if(!$user){
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
            try { $routeUrl = route($module->Route); } catch (\Throwable $e) { $routeUrl = 'javascript:void(0)'; }
        }

        $item = [
            'id' => $module->ModuleID,
            'name' => $module->Name,
            'icon' => $module->Icon ?? 'fa fa-circle-o',
            'route' => $routeUrl,
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
}
