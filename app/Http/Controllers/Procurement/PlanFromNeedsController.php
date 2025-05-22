<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\Procurement\PlanLineItems;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\Model\UserActorTrait;
use App\Models\Auth\User;

class PlanFromNeedsController extends Controller
{
    // Show index page
    public function index()
    {
        return view('procurement.procurementplan.planconsolidation.loadfromneeds.index');
    }

    // Show form to select approved needs
    public function create()
    {
        $approvedNeeds = DepartmentNeeds::with(['item', 'branch', 'department'])
            ->where('Status', 'a')
            ->get();

            $plans = \App\Models\Procurement\ConsolidatedProcurementPlan::select('PlanID', 'Title', 'FiscalYear')->get();
            $branches = \App\Models\Core\Branch::all();
            $departments = \App\Models\HRM\Department::all();

            $categories = \App\Models\Procurement\ItemCategory::select('Id', 'Name')->orderBy('Name')->get();

        return view('procurement.procurementplan.planconsolidation.loadfromneeds.create', compact('approvedNeeds', 'plans', 'branches', 'departments', 'categories'));
    }

    // Store selected needs into the PlanLineItems table
 public function store(Request $request)
{
    //dd($request->all());
    $request->validate([
        'plan_id' => 'required|integer',
        'selected_needs' => 'required|array',
        'category_id' => 'required|integer',
    ]);

    $selectedNeeds = DepartmentNeeds::whereIn('Id', $request->selected_needs)->get();

    foreach ($selectedNeeds as $need) {
       // dd($need->RequestedDate);

        PlanLineItems::create([
            'PlanID' => $request->plan_id,
            'ItemID' => $need->ItemID,
            'BranchID' => $need->BranchID,
            'DepartmentID' => $need->DepartmentID,
            'CategoryID' => $request->category_id,
            'MergedQty' => $need->RequestedQty,
            'EstimatedUnitCost' => $need->EstimatedUnitCost,
            'CreatedBy' => Auth::id(),
            'AdjustedCost' => 0,
            'UnitOfMeasure' => $need->UnitOfMeasure ?? 'Unit',
            'ProcurementMethod' => 'Open Tender',
            'SchedulePeriod' => $need->FiscalYear,
            'ExpectedDeliveryDate' => \Carbon\Carbon::parse($need->RequestedDate),
            'BudgetLineID' => $need->BudgetLineID ?? 0,
            'ExecutionStatus' => 'Pending',
            'ChangeRemarks' => $need->Justification,
            'IsDeleted' => 0,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ]);
        
    }

        return redirect()->back()->with('success', 'Selected needs successfully included in the draft plan.');
    }
}
