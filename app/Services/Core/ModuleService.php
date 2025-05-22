<?php

namespace App\Services\Core;

use App\Models\Core\Module;
use Illuminate\Support\Collection;

class ModuleService
{

    public static function generateNavbar(): Collection
    {
        //return Cache::remember('navbar_modules', 3600, static function () {

        $modules = Module::all();

        $topLevelModules = $modules->whereNull('ParentID')->sortBy('ModuleID');

        $navbar = collect();

        foreach ($topLevelModules as $module) {
            $navbar->push(self::buildNavbarItem($module, $modules));
        }

        return $navbar;
        //  });
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
        $item = [
            'id' => $module->ModuleID,
            'name' => $module->Name,
            'icon' => $module->Icon ?? 'fa fa-circle-o',
            'route' => is_string($module->Route) ? route($module->Route) : 'javascript:void(0)',
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
