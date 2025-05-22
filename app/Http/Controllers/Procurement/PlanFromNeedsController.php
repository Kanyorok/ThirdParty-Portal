<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\Procurement\PlanLineItems;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            ->where('Status', 'Approved')
            ->get();

            $plans = \App\Models\Procurement\ConsolidatedProcurementPlan::select('PlanID', 'Title', 'FiscalYear')->get();
            $branches = \App\Models\Core\Branch::all();
            $departments = \App\Models\HRM\Department::all();

        return view('procurement.procurementplan.planconsolidation.loadfromneeds.create', compact('approvedNeeds', 'plans', 'branches', 'departments'));
    }

    // Store selected needs into the PlanLineItems table
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'selected_needs' => 'required|array',
        ]);

        DB::transaction(function () use ($request) {
            $selectedNeeds = DepartmentNeeds::whereIn('Id', $request->selected_needs)->get();

            foreach ($selectedNeeds as $need) {
                PlanLineItems::create([
                    'PlanID' => $request->plan_id,
                    'ItemID' => $need->ItemID,
                    'BranchID' => $need->BranchID,
                    'DepartmentID' => $need->DepartmentID,
                    'MergedQty' => $need->RequestedQty,
                    'EstimatedUnitCost' => $need->EstimatedUnitCost,
                    'CreatedBy' => Auth::id(),
                ]);
            }
        });

        return redirect()->back()->with('success', 'Selected needs successfully included in the draft plan.');
    }
}
