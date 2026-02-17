<?php

namespace App\Http\Controllers\Procurement;

use App\Enums\Procurement\DepartmentNeedsEnum;
use App\Enums\ProcurementPlanStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\PlanFromNeedsRequest;
use App\Models\Budget\BudgetLine;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use App\Models\Procurement\DepartmentNeed;
use App\Models\Procurement\PlanLineItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanFromNeedsController extends Controller
{
    // Load main view with filters and initial needs
    public function create(Request $request)
    {
        $this->authorize('create', PlanLineItem::class);
        $approvedNeeds = $this->getFilteredNeeds($request);

        $plans = ConsolidatedProcurementPlan::where('Status', ProcurementPlanStatusEnum::Draft)->select('PlanID', 'Title', 'FiscalYear')->get();
        $branches = Branch::all();
        $departments = Department::all();
        $budgetLines = BudgetLine::all();

        $categoryIds = $approvedNeeds->pluck('item.Category')->filter()->unique();
        $categories = ItemCategories::whereIn('Id', $categoryIds)->orderBy('Name')->get();

        return view('procurement.procurementplan.planconsolidation.loadfromneeds.create', compact(
            'approvedNeeds',
            'plans',
            'branches',
            'departments',
            'categories',
            'budgetLines'
        ));
    }

    // AJAX endpoint to return filtered needs
    public function filterNeeds(Request $request): string
    {
        $approvedNeeds = $this->getFilteredNeeds($request);

        return view('procurement.procurementplan.planconsolidation.loadfromneeds.create', compact('approvedNeeds'))->render();
    }

    // Utility to apply filters
    private function getFilteredNeeds(Request $request)
    {
        $query = DepartmentNeed::with(['item', 'branch', 'department'])->where('Status', DepartmentNeedsEnum::Approved)->where('IsUsed', false);

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
    public function store(PlanFromNeedsRequest $request)
    {
        $this->authorize('store', PlanLineItem::class);
        $user = $request->user();

        $selectedNeeds = DepartmentNeed::whereIn('Id', $request->selected_needs)->with('item')->get();

        $noFilters = ! $request->filled('branch_filter') && ! $request->filled('department_filter') && ! $request->filled('category_id');

        foreach ($selectedNeeds as $need) {
            $budgetLineId = $request->budget_line_id[$need->Id] ?? null;
            $unitOfMeasureId = $need->item?->uom?->Id;

            if ($noFilters) {
                // Use values directly from the need (no filters applied)
                $branchId = $need->BranchID;
                $departmentId = $need->DepartmentID;
                $categoryId = $need->item?->Category;
            } else {
                // Use filter values, fallback to need's values if not provided
                $branchId = $request->branch_filter ?? $need->BranchID;
                $departmentId = $request->department_filter ?? $need->DepartmentID;
                $categoryId = $request->category_id ?? $need->item?->Category;
            }

            // Create the plan line item
            PlanLineItem::create([
                'PlanID' => $request->plan_id,
                'ItemID' => $need->ItemID,
                'BranchID' => $branchId,
                'DepartmentID' => $departmentId,
                'CategoryID' => $categoryId,
                'MergedQty' => $need->RequestedQty,
                'EstimatedUnitCost' => $need->EstimatedUnitCost,
                'CreatedBy' => Auth::id(),
                'AdjustedCost' => 0,
                'UnitOfMeasure' => $unitOfMeasureId,
                'ProcurementMethod' => '',
                'SchedulePeriod' => $request->fiscal_year ?? now()->year,
                'ExpectedDeliveryDate' => Carbon::parse($need->RequestedDate),
                'BudgetLineID' => $budgetLineId ? (int)$budgetLineId : null,
                'ExecutionStatus' => 'Pending',
                'ChangeRemarks' => $need->Justification,
                'IsDeleted' => 0,
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
                'SourceType' => 'needs',
                'OriginalQTY' => $need->RequestedQty,
            ]);
            $need->update(['IsUsed' => true]);
            activity()
                ->causedBy($user)
                ->performedOn($need)
                ->event('create')
                ->log('Created plan line item for need ID: ' . $need->id);
        }

        return redirect()->route('procurementplanmaintain.index')->with('success', 'Selected needs successfully included in the draft plan.');
    }
}
