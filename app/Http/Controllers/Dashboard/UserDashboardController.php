<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Dashboard\DashboardWidget;
use App\Models\Dashboard\UserDashboardWidget;
use Illuminate\Http\Request;

class UserDashboardController extends Controller
{
    public function widgets(Request $request)
    {
        $available = DashboardWidget::query()->where('IsActive', true)->get()
            ->map(function($w){
                return [
                    'key' => $w->Key,
                    'name' => $w->Name,
                    'description' => $w->Description,
                    'view' => $w->View,
                    'default_w' => (int)$w->DefaultW,
                    'default_h' => (int)$w->DefaultH,
                ];
            });
        $layout = UserDashboardWidget::query()
            ->where('user_id', $request->user()->Id)
            ->orderBy('sort_order')
            ->get();
        return response()->json([
            'available' => $available,
            'layout' => $layout,
        ]);
    }

    public function saveLayout(Request $request)
    {
        $data = $request->validate([
            'widgets' => 'required|array',
            'widgets.*.widget_key' => 'required|string',
            'widgets.*.x' => 'required|integer|min(0)',
            'widgets.*.y' => 'required|integer|min(0)',
            'widgets.*.w' => 'required|integer|min:1',
            'widgets.*.h' => 'required|integer|min:1',
            'widgets.*.sort_order' => 'nullable|integer|min(0)',
            'widgets.*.config' => 'nullable|array',
        ]);

        $userId = $request->user()->Id;
        // replace current layout
        UserDashboardWidget::where('user_id', $userId)->delete();
        $insert = [];
        foreach ($data['widgets'] as $w) {
            $insert[] = [
                'user_id' => $userId,
                'widget_key' => $w['widget_key'],
                'x' => $w['x'],
                'y' => $w['y'],
                'w' => $w['w'],
                'h' => $w['h'],
                'sort_order' => $w['sort_order'] ?? 0,
                'config' => json_encode($w['config'] ?? []),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($insert)) {
            UserDashboardWidget::insert($insert);
        }
        return response()->json(['status' => 'ok']);
    }
}
