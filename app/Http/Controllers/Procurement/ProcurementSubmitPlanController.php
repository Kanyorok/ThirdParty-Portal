<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\ProcurementPlanStatusEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Services\Procurement\ProcurementPlan\SubmitPlanService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ProcurementSubmitPlanController extends Controller
{
    //
    public function index()
    {
        $draftedplans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->get();
        return view('procurement.procurementplan.submitplan.index', compact('draftedplans'));
    }

    public function create($PlanId)
    {
        $plan = ConsolidatedProcurementPlan::with([
            'lineItems.item',
            'lineItems.setMethod',
            'lineItems.budgetline',
            'lineItems.schedulePlan.periods',
            'creator'
        ])->findOrFail($PlanId);

        return view('procurement.procurementplan.submitplan.create', compact('plan'));
    }

    public function update(Request $request, ConsolidatedProcurementPlan $plan): RedirectResponse
    {
        $lock = Cache::lock('submitted-Plan-' . $plan->PlanID, 5);
        if (!$lock->get()) {
            return redirect()
                ->back()
                ->with('error', 'Plan has been submitted, or another user is working on it.');
        }

        $actor = $request->user();

        try {
            DB::transaction(function () use ($plan, $actor) {
                (new SubmitPlanService($plan))->submit($actor);
            });
        } catch (ErroredException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (Exception $e) {
            \Log::error('Error Plan submission failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        }

        return redirect()->route('Procurement-Plan-Submission.index')->with('success', 'Plan submitted successfully.');
    }


}
