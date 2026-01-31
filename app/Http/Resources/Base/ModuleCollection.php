<?php

namespace App\Http\Resources\Base;

use App\Models\Core\Module;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ModuleCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->transform(function (Module $module) {
                $link = 'javascript:void(0)';
                if ($module->Route && \Illuminate\Support\Facades\Route::has($module->Route)) {
                    $link = route($module->Route);
                }
                $icon = $module->Icon;
                if (empty($icon)) {
                    $icon = $module->parent->Icon;
                    if (empty($icon)) {
                        $icon = '<i class="fa fa-cube"></i>';
                    }
                }

                return [
                    'id' => $module->ModuleID,
                    'name' => $module->Name,
                    'icon' => $icon,
                    'description' => $module->Description ?? '',
                    'link' => $link,
                ];
            }),
        ];
    }
}
