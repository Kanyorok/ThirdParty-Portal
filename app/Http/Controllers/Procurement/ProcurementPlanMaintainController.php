<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Core\PostingEnum;
use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\PlanLineItem;
use Illuminate\Http\Request;

class ProcurementPlanMaintainController extends Controller
{
    //
    public function index()
    {
        $this->authorize('viewAny', ConsolidatedProcurementPlan::class);
        $plans = ConsolidatedProcurementPlan::with(['createdBy', 'lineItems'])->get();

        return view('procurement.procurementplan.procurementplanmaintenance.index', compact('plans'));
    }

    public function getEstimatedCostAttribute()
    {
        return $this->lineItems->sum(fn($item) => $item->MergedQty * $item->EstimatedUnitCost);
    }

    public function store(Request $request)
    {
        $this->authorize('store', ConsolidatedProcurementPlan::class);
        $request->validate([
            'Title' => 'required|string|max:255',
            'FiscalYear' => 'required|integer',
            'Status' => 'required|string',
            'CreatedBy' => 'required|integer',
        ]);

        $userId = $request->CreatedBy;
        $user = User::find($userId);

    $plan = ConsolidatedProcurementPlan::create([
        'Title'           => $request->Title,
        'ReferenceNumber' => 'PLAN/' . $request->FiscalYear . '/' . rand(100, 999),
        'FiscalYear'      => $request->FiscalYear,
        'Status' => ProcurementPlanStatusEnum::Draft,
        'CreatedBy'       => $userId,
        'SubmittedBy' => $userId,
        'CreatedDate'     => now(),
        'SubmittedDate'   => now(),
        'CreatedOn' => now(),
        'ModifiedOn'      => now(),
        'CurrentApprLevel'=> 0,
        'ModifiedBy' => $userId,
    ]);

        activity()->causedBy($user)->performedOn($plan)->event('create')->log('created plan ' . $plan->Id);
        // Redirect to manual entry page with the new plan ID
        return redirect()->route('procurementplanmaintain.index')
            ->with('success', 'Plan created successfully. You may now proceed to add line items.');

    }
    public function create(){
        $this->authorize('create', ConsolidatedProcurementPlan::class);
        return view('procurement.procurementplan.procurementplanmaintenance.create');

    }

    public function editDraft($plan_id)
    {
        $this->authorize('editDraft', ConsolidatedProcurementPlan::class);
        $draftItems = PlanLineItem::with(['item', 'branch'])
            ->where('PlanID', $plan_id)
            ->where('Status', PostingEnum::Draft)
            ->get();

        // Pass any other data your blade expects, like Plan info or counts if needed

        return view('procurement.procurementplan.planapproval.ammendplan.index', compact('draftItems'));
    }

    public function show($id)
    {
        $this->authorize('view', ConsolidatedProcurementPlan::class);
        $plan = ConsolidatedProcurementPlan::with(['lineItems.item', 'lineItems.budgetLine', 'lineItems.procurementMode', 'createdBy'])->findOrFail($id);

        return view('procurement.procurementplan.procurementplanmaintenance.show', compact('plan'));
    }

}
