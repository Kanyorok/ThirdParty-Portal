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
use Illuminate\Support\Facades\Log;

class ProcurementSubmitPlanController extends Controller
{
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
            //let the service handle transactions
            (new SubmitPlanService($plan))->submit($actor);
            
            $lock->release();
            
            return redirect()
                ->route('Procurement-Plan-Submission.index')
                ->with('success', 'Plan submitted successfully.');
                
        } catch (ErroredException $e) {
            $lock->release();
            Log::error('Plan submission failed (business logic)', [
                'planId' => $plan->PlanID,
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('error', $e->getMessage());
            
        } catch (Exception $e) {
            $lock->release();
            Log::error('Plan submission failed (unexpected)', [
                'planId' => $plan->PlanID,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->back()->with('error', 'Unexpected error, try again later.');
        }
    }
}