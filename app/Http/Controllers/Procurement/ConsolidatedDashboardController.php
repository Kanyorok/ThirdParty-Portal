<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use App\Models\Core\Branch;
use App\Models\HRM\Department;
use App\Models\Procurement\DepartmentNeeds;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class ConsolidatedDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('ajax')->only('show');
    }

    public function index(Request $request)
    {
        $branch = $request->input('branch', 'All Branches');
        $department = $request->input('department', 'All Departments');
        $year = $request->input('year', 'All Years');


        $query = DepartmentNeeds::with(['item', 'branch', 'department']);

        if ($branch !== 'All Branches' && !empty($branch)) {
            $query->where('BranchID', $branch); 
        }

        if ($department !== 'All Departments' && !empty($department)) {
            $query->where('DepartmentID', $department);
        }

        if ($year !== 'All Years') {
            $query->where('FiscalYear', $year);
        }

        $needs = $query->get();

        // Load filter dropdown options
        $branches = Branch::orderBy('Name')->pluck('Name', 'Id')->prepend('All Branches', 'All Branches');
        $departments = Department::orderBy('Name')->pluck('Name', 'Id')->prepend('All Departments', 'All Departments');
        $years = ['All Years', 2025, 2026, 2027];

        return view('procurement.procurementplan.planconsolidation.dashboard.index', compact(
            'needs', 'branches', 'departments', 'years', 'branch', 'department', 'year'
        ));
    }

    public function show($needId): JsonResponse
    {
        $needs = DepartmentNeeds::with('item')
            ->where('NeedID', $needId)
            ->get()
            ->map(function ($need) {
                return [
                    'ItemName' => $need->item?->ItemName ?? 'N/A',
                    'BranchName' => $need->branch?->Name ?? 'N/A',
                    'DepartmentName' => $need->department?->Name ?? 'N/A',
                    'RequestedQty' => $need->RequestedQty,
                    'EstimatedCost' => number_format($need->RequestedQty * $need->EstimatedUnitCost, 2),
                    'CreatedOn' => $need->CreatedOn->format('Y-m-d'),
                    'Status' => $need->Status->label(),
                ];
            });

        return $this->succeeded('ok', data: $needs);
    }

    public function create()
    {
        // Fetch branches and departments for the create form dropdowns
        $branches = DepartmentNeed::distinct()->pluck('branch');
        $departments = DepartmentNeed::distinct()->pluck('department');

        return view('procurement.procurementplan.planconsolidation.dashboard.create', compact('branches', 'departments'));
    }
}
