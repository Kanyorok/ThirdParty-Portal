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
        // Normalize inputs: treat empty / 'all' (case-insensitive) as null (no filter)
        $branchInput = trim((string)$request->input('branch', ''));
        $departmentInput = trim((string)$request->input('department', ''));
        $yearInput = trim((string)$request->input('year', ''));

        $branch = $branchInput === '' ? null : $branchInput; // expecting numeric id
        $department = $departmentInput === '' ? null : $departmentInput; // numeric id
        $year = (strcasecmp($yearInput, 'All Years') === 0 || $yearInput === '') ? null : $yearInput;

        $query = DepartmentNeed::with(['item', 'branch', 'department']);

        // Status filtering
        $statusInput = trim((string)$request->input('status', ''));
        $status = $statusInput === '' ? null : $statusInput;
        if ($status) {
            // Accept either enum name (Approved) or value (a/r/p)
            $possible = [
                strtolower($status) => $status,
                strtoupper($status) => $status,
            ];
            // Map common textual inputs to enum values
            $map = [
                'approved' => \App\Enums\Procurement\DepartmentNeedsEnum::Approved->value,
                'rejected' => \App\Enums\Procurement\DepartmentNeedsEnum::Rejected->value,
                'pending'  => \App\Enums\Procurement\DepartmentNeedsEnum::Pending->value,
            ];
            $lower = strtolower($status);
            if (isset($map[$lower])) {
                $query->where('Status', $map[$lower]);
            } else {
                // If user passed raw value (a/r/p)
                $query->where('Status', $status);
            }
        }

        if ($branch) {
            $query->where('BranchID', $branch);
        }
        if ($department) {
            $query->where('DepartmentID', $department);
        }
        if ($year) {
            // Filter by year component of RequestedDate
            $query->whereYear('RequestedDate', $year);
        }

        $needs = $query->orderByDesc('CreatedOn')->get();

        // Load filter dropdown options (prepend blank for all)
        $branches = \App\Models\Core\Branch::orderBy('Name')->pluck('Name', 'Id');
        $departments = \App\Models\HRM\Department::orderBy('Name')->pluck('Name', 'Id');
        // Dynamic years from RequestedDate
        $yearsCollection = DepartmentNeed::query()
            ->selectRaw('DISTINCT YEAR(RequestedDate) as yr')
            ->whereNotNull('RequestedDate')
            ->orderBy('yr')
            ->pluck('yr');
        $years = $yearsCollection->toArray();

        // Status dropdown options using enum labels
        $statusOptions = collect(\App\Enums\Procurement\DepartmentNeedsEnum::cases())
            ->mapWithKeys(function ($case) {
                return [$case->value => $case->label()];
            })->toArray();

        return view('procurement.procurementplan.planconsolidation.dashboard.index', compact(
            'needs',
            'branches',
            'departments',
            'years',
            'branch',
            'department',
            'year',
            'status',
            'statusOptions'
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
