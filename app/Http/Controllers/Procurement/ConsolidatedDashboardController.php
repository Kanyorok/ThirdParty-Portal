<?php

namespace App\Http\Controllers\Procurement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Procurement\DepartmentNeeds;
use App\Models\HRM\Department;
use App\Models\Core\Branch;


class ConsolidatedDashboardController extends Controller
{
    public function index(Request $request)
{
    $branch = $request->input('branch', 'All Branches');
    $department = $request->input('department', 'All Departments');
    $year = $request->input('year', 'All Years');
    

    $query = DepartmentNeeds::query();

if ($branch !== 'All Branches' && $branch !== null && $branch !== '') {
    $query->where('BranchID', $branch);
}

if ($department !== 'All Departments' && $department !== null && $department !== '') {
    $query->where('DepartmentID', $department);
}


    if ($year !== 'All Years') {
    $query->where('FiscalYear', $year);
}


    $needs = $query->get();

    // Fetch filters
    $branches = Branch::orderBy('Name')->pluck('Name', 'BranchID')->prepend('All Branches', 'All Branches');
    $departments = Department::orderBy('Name')->pluck('Name', 'DepartmentID')->prepend('All Departments', 'All Departments');
    $years = ['All Years', 2025, 2026, 2027];

    return view('procurement.procurementplan.planconsolidation.dashboard.index', compact(
        'needs', 'branches', 'departments', 'years', 'branch', 'department', 'year'
    ));
}
public function show($id)
{
    $need = DepartmentNeeds::with(['branch', 'department', 'item'])->findOrFail($id);

    return response()->json([
        'ItemName' => $need->item->ItemName ?? 'N/A',
        'BranchName' => $need->branch->Name ?? 'N/A',
        'DepartmentName' => $need->department->Name ?? 'N/A',
        'RequestedQty' => $need->RequestedQty,
        'EstimatedCost' => number_format($need->RequestedQty * $need->EstimatedUnitCost, 2),
        'CreatedOn' => \Carbon\Carbon::parse($need->CreatedOn)->format('Y-m-d'),
        'Status' => $need->Status ?? 'Pending',
    ]);
}

    public function create()
    {
        // Fetch branches and departments for the create form dropdowns
        $branches = DepartmentNeed::distinct()->pluck('branch');
        $departments = DepartmentNeed::distinct()->pluck('department');

        return view('procurement.procurementplan.planconsolidation.dashboard.create', compact('branches', 'departments'));
    }
}
