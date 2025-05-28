<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Enums\Procurement\SchedulePlanEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\procurement\SchedulePlanRequest;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItems;
use App\Services\Procurement\ProcurementPlan\SchedulePlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Throwable;

class ProcurementSchedulePlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only('fetchLinesByDPlan');
    }

    public function index()
    {
        $draftedplans = ConsolidatedProcurementPlan::where('Status', PostingEnum::Draft)->get();
        return view('procurement.procurementplan.scheduleplan.index', compact('draftedplans'));
    }

    public function fetchLinesByDPlan($planId): JsonResponse
    {
        try {
            $Lines = PlanLineItems::with(['item', 'schedulePlan.periods']) // Include periods
            ->where('PlanID', $planId)
                ->get();


            return $this->succeeded('ok', data: $Lines->map(function ($lineItem) {
                $statusEnum = $lineItem->schedulePlan?->Status ?? SchedulePlanEnum::NotScheduled;

                return [
                    'LineItemID' => $lineItem->LineItemID,
                    'item_name' => optional($lineItem->item)->ItemName,
                    'MergedQty' => $lineItem->MergedQty,
                    'ScheduleQTY' => optional($lineItem->schedulePlan)->ScheduleQTY,
                    'Status' => $statusEnum->label(),
                    'Periods' => $lineItem->schedulePlan?->periods ?? [], // Optional: return schedule breakdown
                ];
            }));

        } catch (Throwable $e) {
            Log::error('Error in fetchLinesByDPlan: ' . $e->getMessage());
            return $this->errored('error occurred, try again later.');
        }
    }

    public function store(SchedulePlanRequest $request, SchedulePlanService $schedulePlanService)
    {
        $actor = $request->user();
        $consolidatedPlan = $request->getPlan();

        //fail a redirect back with statate

        foreach ($consolidatedPlan->lineItems as $planLineItem) {

            $mode = $request->input("mode_$lineItemId");

            $totalQty = 0;
            $periods = [];

            if ($mode === 'quarter') {
                $q1 = $request->getQuarterOne($lineItemId);

                $q2 = (int)$request->input("q2_$lineItemId", 0);
                $q3 = (int)$request->input("q3_$lineItemId", 0);
                $q4 = (int)$request->input("q4_$lineItemId", 0);
                $periods = [
                    'Q1' => $q1,
                    'Q2' => $q2,
                    'Q3' => $q3,
                    'Q4' => $q4,
                ];
                $totalQty = (int)bcadd($q1, bcadd($q2, bcadd($q3, $q4)));
            } elseif ($mode === 'month') {
                $months = ['JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN', 'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'];
                foreach ($months as $month) {
                    $inputKey = strtolower($month) . "_$lineItemId";
                    $qty = (int)$request->input($inputKey, 0);
                    $periods[$month] = $qty;
                    $totalQty += $qty;
                }
            }

            $mergedQty = $planLineItem->MergedQty ?? 0;

            // Determine schedule status
            if ($totalQty === 0) {
                $status = SchedulePlanEnum::NotScheduled;
            } elseif ($totalQty < $mergedQty) {
                $status = SchedulePlanEnum::PartiallyScheduled;
            } elseif ($totalQty === $mergedQty) {
                $status = SchedulePlanEnum::FullyScheduled;
            } else {
                $status = SchedulePlanEnum::PartiallyScheduled; // fallback for excess
            }

            // Package all schedule data
            $data = [
                'ScheduleQTY' => $totalQty,
                'Status' => $status,
                'periods' => $periods,
            ];

            // Save schedule and periods
            $schedulePlanService->create($data, $actor, $consolidatedPlan, $planLineItem);
        }

        return redirect()->route('Procurement-Plan-Schedule.index')->with('success', 'Schedules saved successfully.');
    }

    public function create(Request $request)
    {
        $planId = $request->input('plan_id');
        $plan = ConsolidatedProcurementPlan::with('lineItems.item')->where('PlanID', $planId)->first();
        if (!$plan instanceof ConsolidatedProcurementPlan) {
            return redirect()->back()->with('fail', 'Plan is not in draft status.');
        }
        return view('procurement.procurementplan.scheduleplan.create', compact('plan'));
    }

    public function edit($lineItemId, Request $request)
    {
        $planId = $request->query('plan_id');
        $plan = ConsolidatedProcurementPlan::findOrFail($planId);
        $lineItem = PlanLineItems::with(['item', 'schedulePlan.periods'])->findOrFail($lineItemId);

        return view('procurement.procurementplan.scheduleplan.edit', compact('plan', 'lineItem'));
    }

}
