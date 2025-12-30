<?php

namespace App\Http\Controllers\Auth;

use App\Enums\LeadStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Budget\Budget;
use App\Models\Budget\BudgetGLMaster;
use App\Models\CRM\Lead;
use App\Models\Dashboard\DashboardWidget;
use App\Models\Dashboard\UserDashboardWidget;
use App\Models\Procurement\DepartmentNeed;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected const int MONTHS = 6;

    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @return View
     */
    public function __invoke(Request $request): View
    {
        $actor = $request->user();
        //$actorId = $actor?->Id ?? 1; DON'T DO THIS, CHECK IF USER IS NOT LOGOUT.
        $data = [
            'leads'     => [
                'line'  => [
                    'labels'    => [],
                    'converted' => [],
                ],
                'donut' => ['labels' => []],
                'total' => 0,
            ],
            'campaigns' => [
                'active' => 0,
                'sent'   => 0,
            ],
            'schedule'  => [
                'calls'        => 0,
                'appointments' => 0,
                'total'        => 0,
            ],
            'tickets'   => ['active' => 0],
        ];

        //Fetch Number of open budgets, Total GLS
        $openBudgets = Budget::where('Status', 'draft')->count();
        $totalGLS = BudgetGLMaster::count();

        // Widgets: ensure base widgets exist
        $this->ensureDefaultWidgets($actor->Id);

        // Build simple stats for widgets
        $needsTotal = DepartmentNeed::count();
        $needsApproved = DepartmentNeed::where('Status', \App\Enums\Procurement\DepartmentNeedsEnum::Approved->value)->count();
        $needsPending = DepartmentNeed::where('Status', \App\Enums\Procurement\DepartmentNeedsEnum::Pending->value)->count();

        $stats = [
            'needs_total' => $needsTotal,
            'needs_approved' => $needsApproved,
            'needs_pending' => $needsPending,
            'pending_approvals' => [
                ['title' => 'Department Needs', 'count' => $needsPending],
            ],
        ];

        // Load available widgets (normalize keys for Blade) and current user layout
        $availableWidgets = DashboardWidget::where('IsActive', true)->orderBy('Name')->get()
            ->map(function ($w) {
                return (object) [
                    'key' => $w->Key,
                    'name' => $w->Name,
                    'view' => $w->View,
                    'module' => $w->Module ?? null,
                    'type' => $w->Type ?? null,
                    'endpoint' => $w->DataEndpoint ?? null,
                    'default_filters' => $w->DefaultFilters ? json_decode($w->DefaultFilters, true) : null,
                    'default_w' => (int) $w->DefaultW,
                    'default_h' => (int) $w->DefaultH,
                ];
            });
        $layout = UserDashboardWidget::where('user_id', $actor->Id)->orderBy('sort_order')->get();
        if ($layout->isEmpty()) {
            // seed default layout for user (non-destructive; only if none)
            $defaults = [
                ['widget_key' => 'procurement_consolidation', 'w' => 6, 'h' => 1],
                ['widget_key' => 'pending_approvals', 'w' => 6, 'h' => 1],
            ];
            $i = 0;
            foreach ($defaults as $d) {
                UserDashboardWidget::create([
                    'user_id' => $actor->Id,
                    'widget_key' => $d['widget_key'],
                    'x' => 0,
                    'y' => $i,
                    'w' => $d['w'],
                    'h' => $d['h'],
                    'sort_order' => $i,
                    'config' => [],
                ]);
                $i++;
            }
            $layout = UserDashboardWidget::where('user_id', $actor->Id)->orderBy('sort_order')->get();
        }

        return view('auth.dashboard', compact(
            'data',
            'openBudgets',
            'totalGLS',
            'availableWidgets',
            'layout',
            'stats'
        ));
    }

    protected function months(): array
    {
        $dates = collect();
        $dateTime = Carbon::now()->startOfMonth()->subMonths(self::MONTHS);
        for ($i = 1; $i <= self::MONTHS; $i++) {
            $dateTime->addMonth();
            $dates->add($dateTime->format('M y'));
        }
        return $dates->toArray();
    }

    private function converted(User $actor): array
    {
        $data = collect();
        $dateTime = Carbon::now()->startOfMonth()->subMonths(self::MONTHS);
        for ($i = 1; $i <= self::MONTHS; $i++) {
            $dateTime->addMonth();
            $converted = 0;
            try {
                $converted = Lead::withTrashed()->whereBetween('DeletedOn', [$dateTime->copy()->startOfMonth(), $dateTime->copy()->endOfMonth()])
                    ->where('Status', LeadStatusEnum::Won->value)->where('RelationshipManagerID', $actor->Id)->count();
            } catch (Exception) {
            }
            $data->add($converted);
        }

        return $data->toArray();
    }

    private function ensureDefaultWidgets(int $actorId): void
    {
        $defaults = [
            [
                'key' => 'procurement_consolidation',
                'name' => 'Procurement Consolidation',
                'description' => 'Summary of procurement needs',
                'view' => 'dashboard.widgets.procurement_consolidation',
                'module' => 'Procurement',
                'type' => 'KPI',
                'default_w' => 6,
                'default_h' => 1,
            ],
            [
                'key' => 'pending_approvals',
                'name' => 'Pending Approvals',
                'description' => 'Items waiting for your approval',
                'view' => 'dashboard.widgets.pending_approvals',
                'module' => 'Procurement',
                'type' => 'Workflow',
                'default_w' => 6,
                'default_h' => 1,
            ],
        ];
        foreach ($defaults as $w) {
            DashboardWidget::updateOrCreate(
                ['Key' => $w['key']],
                [
                    'Name' => $w['name'],
                    'Description' => $w['description'],
                    'View' => $w['view'],
                    'Module' => $w['module'] ?? null,
                    'Type' => $w['type'] ?? null,
                    'DefaultW' => $w['default_w'],
                    'DefaultH' => $w['default_h'],
                    'IsActive' => true,
                    'CreatedBy' => $actorId,
                    'CreatedOn' => now(),
                    'ModifiedBy' => $actorId,
                    'ModifiedOn' => now(),
                ]
            );
        }
    }

    /**
     * Get available widgets for the user dashboard
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWidgets(Request $request)
    {
        $availableWidgets = DashboardWidget::where('IsActive', true)->orderBy('Name')->get()
            ->map(function ($w) {
                return [
                    'key' => $w->Key,
                    'name' => $w->Name,
                    'view' => $w->View,
                    'module' => $w->Module ?? null,
                    'type' => $w->Type ?? null,
                    'endpoint' => $w->DataEndpoint ?? null,
                    'default_filters' => $w->DefaultFilters ? json_decode($w->DefaultFilters, true) : null,
                    'default_w' => (int) $w->DefaultW,
                    'default_h' => (int) $w->DefaultH,
                ];
            });

        return response()->json([
            'available' => $availableWidgets,
        ]);
    }

    /**
     * Save the user's dashboard layout
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveLayout(Request $request)
    {
        $user = $request->user();
        $widgets = $request->input('widgets', []);

        // Delete existing layout for the user
        UserDashboardWidget::where('user_id', $user->Id)->delete();

        // Save new layout
        foreach ($widgets as $widget) {
            UserDashboardWidget::create([
                'user_id' => $user->Id,
                'widget_key' => $widget['widget_key'],
                'x' => $widget['x'] ?? 0,
                'y' => $widget['y'] ?? 0,
                'w' => $widget['w'] ?? 6,
                'h' => $widget['h'] ?? 1,
                'sort_order' => $widget['sort_order'] ?? 0,
                'config' => $widget['config'] ?? [],
            ]);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Layout saved successfully',
        ]);
    }
}
