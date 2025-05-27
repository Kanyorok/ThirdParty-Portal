<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\Procurement\PlanLineItems;
use App\Models\Procurement\BudgetMaster;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\Model\UserActorTrait;
use App\Models\Auth\User;

class PlanFromNeedsController extends Controller
{
    // Load main view with filters and initial needs
    public function create(Request $request)
    {
        $approvedNeeds = $this->getFilteredNeeds($request);

        $plans = \App\Models\Procurement\ConsolidatedProcurementPlan::select('PlanID', 'Title', 'FiscalYear')->get();
        $branches = \App\Models\Core\Branch::all();
        $departments = \App\Models\HRM\Department::all();
        $categories = \App\Models\Procurement\ItemCategory::select('Id', 'Name')->orderBy('Name')->get();
        $budgetLines = \App\Models\Procurement\BudgetMaster::all();

         $categoryIds = $approvedNeeds->pluck('item.Category')->filter()->unique();
         $categories = \App\Models\Procurement\ItemCategory::whereIn('Id', $categoryIds)->orderBy('Name')->get();

        return view('procurement.procurementplan.planconsolidation.loadfromneeds.create', compact(
            'approvedNeeds', 'plans', 'branches', 'departments', 'categories', 'budgetLines'
        ));
    }

    // AJAX endpoint to return filtered needs
    public function filterNeeds(Request $request)
    {
        $approvedNeeds = $this->getFilteredNeeds($request);
        return view('procurement.procurementplan.planconsolidation.loadfromneeds.partials.needs_list', compact('approvedNeeds'))->render();
    }

    // Utility to apply filters
    private function getFilteredNeeds(Request $request)
    {
        $query = DepartmentNeeds::with(['item', 'branch', 'department'])->where('Status', 'a');

        if ($request->filled('branch_filter')) {
            $query->where('BranchID', $request->branch_filter);
        }

        if ($request->filled('department_filter')) {
            $query->where('DepartmentID', $request->department_filter);
        }

         if ($request->filled('category_id')) {
            $query->whereHas('item', function ($q) use ($request) {
                $q->where('Category', $request->category_id);
            });
        }

        return $query->get();
    }

    // Store selected needs
    public function store(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|integer',
            'category_id' => 'required|integer',
            'selected_needs' => 'required|array|min:1',
            'budget_line_id' => 'required|array',
        ]);

        $selectedNeeds = DepartmentNeeds::whereIn('Id', $request->selected_needs)->get();

        foreach ($selectedNeeds as $need) {
            $budgetLineId = $request->budget_line_id[$need->Id] ?? null;

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
                'SchedulePeriod' => $request->fiscal_year ?? now()->year,
                'ExpectedDeliveryDate' => \Carbon\Carbon::parse($need->RequestedDate),
                'BudgetLineID' => $budgetLineId ?? 0,
                'ExecutionStatus' => 'Pending',
                'ChangeRemarks' => $need->Justification,
                'IsDeleted' => 0,
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        }

        return redirect()->back()->with('success', 'Selected needs successfully included in the draft plan.');
    }
}