<?php

namespace App\Http\Controllers\Core;

use App\Http\Controllers\Controller;
use App\Services\Core\ModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GlobalSearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        if (! $query || strlen($query) < 2) {
            return response()->json([]);
        }

        $modules = ModuleService::getNavbarData();

        $results = $this->flattenAndFilter($modules, $query);

        return response()->json($results->values());
    }

    private function flattenAndFilter(Collection $modules, string $query): Collection
    {
        $results = collect();
        $lowerQuery = Str::lower($query);

        foreach ($modules as $module) {
            // Check current module
            if (
                Str::contains(Str::lower($module['name']), $lowerQuery) ||
                Str::contains(Str::lower($module['description'] ?? ''), $lowerQuery)
            ) {
                $route = $module['route'] ?? '#';
                if ($route !== '#' && $route !== 'javascript:void(0)') {
                    $results->push([
                       'name' => $module['name'],
                       'route' => $route,
                       'icon' => $module['icon'] ?? null,
                       'breadcrumb' => $module['name'],
                    ]);
                }
            }

            // Check children
            if (! empty($module['children'])) {
                $childrenResults = $this->flattenAndFilter(collect($module['children']), $query);
                // Prepend parent name to breadcrumb for better context
                foreach ($childrenResults as $child) {
                    $child['breadcrumb'] = $module['name'] . ' > ' . $child['breadcrumb'];
                    $results->push($child);
                }
            }
        }

        return $results;
    }
}
