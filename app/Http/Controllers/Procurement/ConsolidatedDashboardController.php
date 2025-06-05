<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\HRM\Department;
use App\Models\Core\Branch;
use App\Policies\Procurement\ConsolidatedProcurementPlanPolicy;


class ConsolidatedDashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', DepartmentNeeds::class);
        $branch = $request->input('branch', 'All Branches');
        $department = $request->input('department', 'All Departments');
        $year = $request->input('year', 'All Years');


        $query = DepartmentNeeds::with(['item', 'branch', 'department']);

        if ($branch !== 'All Branches' && !empty($branch)) {
            $query->where('BranchID', $branch); // ✅ Correct field
        }

        if ($department !== 'All Departments' && !empty($department)) {
            $query->where('DepartmentID', $department); // ✅ Correct field
        }

        if ($year !== 'All Years') {
            $query->where('FiscalYear', $year);
        }

        $needs = $query->get();

        // Load filter dropdown options
        $branches = \App\Models\Core\Branch::orderBy('Name')->pluck('Name', 'Id')->prepend('All Branches', 'All Branches');
        $departments = \App\Models\HRM\Department::orderBy('Name')->pluck('Name', 'Id')->prepend('All Departments', 'All Departments');
        $years = ['All Years', 2025, 2026, 2027];

        return view('procurement.procurementplan.planconsolidation.dashboard.index', compact(
            'needs', 'branches', 'departments', 'years', 'branch', 'department', 'year'
        ));
    }

    public function show($needId)
    {
        $needs = DepartmentNeeds::with('item')
            ->where('NeedID', $needId)
            ->get()
            ->map(function ($need) {
                $this->authorize('view', $need);
                return [
                    'ItemName' => $need->item->ItemName ?? 'N/A',
                    'BranchName' => $need->branch->Name ?? 'N/A',
                    'DepartmentName' => $need->department->Name ?? 'N/A',
                    'RequestedQty' => $need->RequestedQty,
                    'EstimatedCost' => number_format($need->RequestedQty * $need->EstimatedUnitCost, 2),
                    'CreatedOn' => \Carbon\Carbon::parse($need->CreatedOn)->format('Y-m-d'),
                    'Status' => $need->Status->label(),
                ];
            });

        return response()->json($needs);
    }

    public function create()
    {
        $this->authorize('create', DepartmentNeeds::class);
        // Fetch branches and departments for the create form dropdowns
        $branches = DepartmentNeed::distinct()->pluck('branch');
        $departments = DepartmentNeed::distinct()->pluck('department');

        return view('procurement.procurementplan.planconsolidation.dashboard.create', compact('branches', 'departments'));
    }
}
