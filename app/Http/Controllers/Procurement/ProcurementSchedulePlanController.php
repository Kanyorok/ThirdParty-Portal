<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\SchedulePlanEnum;
use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\ProcurementPlan\SchedulePlanRequest;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItem;
use App\Services\Procurement\ProcurementPlan\SchedulePlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcurementSchedulePlanController extends Controller
{
    protected $schedulePlanService;

    public function __construct(SchedulePlanService $schedulePlanService)
    {
        $this->schedulePlanService = $schedulePlanService;
    }

    public function index()
    {
        $draftedplans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->get();
        return view('procurement.procurementplan.scheduleplan.index', compact('draftedplans'));
    }

    public function create(Request $request)
    {
        $planId = $request->input('plan_id');
        $plan = ConsolidatedProcurementPlan::with('lineItems.item')->findOrFail($planId);
        return view('procurement.procurementplan.scheduleplan.create', compact('plan'));
    }

    public function fetchLinesByDPlan($planId)
    {
        try {
            $Lines = PlanLineItem::with(['item', 'schedulePlan.periods', 'departmentNeed']) // include relationship
            ->where('PlanID', $planId)
                ->get();

            $mappedLines = $Lines->map(function ($lineItem) {
                $statusEnum = $lineItem->schedulePlan?->Status ?? SchedulePlanEnum::NotScheduled;

                $rawType = $lineItem->schedulePlan?->ScheduleType;
                $displayType = match ($rawType) {
                    'month' => 'Monthly',
                    'quarter' => 'Quarterly',
                    default => '-',
                };

                return [
                    'LineItemID' => $lineItem->LineItemID,
                    'item_name' => $lineItem->item?->ItemName,
                    'MergedQty' => $lineItem->MergedQty,
                    'ScheduleQTY' => $lineItem->schedulePlan?->ScheduleQTY,
                    'ScheduleType' => $displayType,
                    'Status' => $statusEnum->label(),
                    'Periods' => $lineItem->schedulePlan?->periods ?? [],
                    'NeedID' => $lineItem->departmentNeed?->NeedID, // ✅ Now included
                ];
            });

            return response()->json($mappedLines);
        } catch (Throwable $e) {
            Log::error('Error in fetchLinesByDPlan: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }

    public function store(SchedulePlanRequest  $request)
    {
        $actor = $request->user();
        $plan = ConsolidatedProcurementPlan::findOrFail($request->input('pending_plan_id'));
        $lineItemIds = $request->input('lineItemIds', []);

        foreach ($lineItemIds as $lineItemId) {
            $lineItem = PlanLineItem::find($lineItemId);
            if (!$lineItem) continue;

            $mode = $request->input("mode_{$lineItemId}");
            if (empty($mode)) continue;

            $totalQty = 0;
            $periods = [];

            if ($mode === 'quarter') {
                for ($q = 1; $q <= 4; $q++) {
                    $qty = (int)$request->input("q{$q}_{$lineItemId}", 0);
                    if ($qty > 0) {
                        $periods["Q{$q}"] = $qty;
                        $totalQty += $qty;
                    }
                }
            } elseif ($mode === 'month') {
                    $periodsInput = $request->input('periods', []);
                    for ($m = 1; $m <= 12; $m++) {
                        $key = "M{$m}_{$lineItemId}";
                        $qty = (int)($periodsInput[$key] ?? 0);
                        if ($qty > 0) {
                            $periods["M{$m}"] = $qty;
                            $totalQty += $qty;
                        }
                    }
                }

            $mergedQty = $lineItem->MergedQty ?? 0;
            if ($totalQty > $mergedQty) {
                return redirect()->back()
                    ->withErrors(["Schedule for '{$lineItem->item->ItemName}' failed: Total ({$totalQty}) exceeds available ({$mergedQty})."])
                    ->withInput();
            }

            if ($totalQty == 0) {
                $status = SchedulePlanEnum::NotScheduled;
            } elseif ($totalQty < $mergedQty) {
                $status = SchedulePlanEnum::PartiallyScheduled;
            } else {
                $status = SchedulePlanEnum::FullyScheduled;
            }

            $dataForService = [
                'ScheduleQTY' => $totalQty,
                'ScheduleType' => $mode,
                'Status' => $status,
                'periods' => !empty($periods) ? $periods : [],
            ];

            $this->schedulePlanService->create($dataForService, $actor, $plan, $lineItem);
        }

        return redirect()->route('Procurement-Plan-Schedule.index')
            ->with('success', 'Schedules saved successfully.');
    }

    public function edit($lineItemId, Request $request)
    {
        $planId = $request->query('plan_id');
        $plan = ConsolidatedProcurementPlan::findOrFail($planId);
        $lineItem = PlanLineItem::with(['item', 'schedulePlan.periods'])->findOrFail($lineItemId);

        return view('procurement.procurementplan.scheduleplan.edit', compact('plan', 'lineItem'));
    }
}
