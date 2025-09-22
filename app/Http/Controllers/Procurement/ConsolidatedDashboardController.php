<?php

namespace App\Http\Controllers\Procurement;

use App\Exports\NeedsExport;
use App\Http\Controllers\Controller;
use App\Models\Procurement\DepartmentNeed;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ConsolidatedDashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', DepartmentNeed::class);
        $branch = $request->input('branch', 'All Branches');
        $department = $request->input('department', 'All Departments');
        $year = $request->input('year', 'All Years');


        $query = DepartmentNeed::with(['item', 'branch', 'department']);

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
        $branches = \App\Models\Core\Branch::orderBy('Name')->pluck('Name', 'Id')->prepend('All Branches', 'All Branches');
        $departments = \App\Models\HRM\Department::orderBy('Name')->pluck('Name', 'Id')->prepend('All Departments', 'All Departments');
        $years = ['All Years', 2025, 2026, 2027];

        return view('procurement.procurementplan.planconsolidation.dashboard.index', compact(
            'needs',
            'branches',
            'departments',
            'years',
            'branch',
            'department',
            'year'
        ));
    }

    public function show($needId)
    {
        $needs = DepartmentNeed::with('item')
            ->where('NeedID', $needId)
            ->get()
            ->map(function ($need) {
                $this->authorize('view', $need);
                return [
                    'NeedID' => $need->NeedID,
                    'ItemName' => $need->item->ItemName ?? 'N/A',
                    'BranchName' => $need->branch->Name ?? 'N/A',
                    'DepartmentName' => $need->department->Name ?? 'N/A',
                    'RequestedQty' => $need->RequestedQty,
                    'EstimatedCost' => number_format($need->RequestedQty * $need->EstimatedUnitCost, 2),
                    'RequestedDate' => \Carbon\Carbon::parse($need->RequestedDate)->format('d/m/Y'),
                    'CreatedOn' => \Carbon\Carbon::parse($need->CreatedOn)->format('Y-m-d'),
                    'Status' => $need->Status->label(),
                ];
            });

        return response()->json($needs);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new NeedsExport($request), 'consolidated_needs.xlsx');
    }

    public function create()
    {
        $this->authorize('create', DepartmentNeed::class);
        // Fetch branches and departments for the create form dropdowns
        $branches = DepartmentNeed::distinct()->pluck('branch');
        $departments = DepartmentNeed::distinct()->pluck('department');

        return view('', compact('branches', 'departments'));
    }
}
