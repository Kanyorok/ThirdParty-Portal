<?php

namespace App\Http\Controllers\Procurement;

use App\Exports\NeedsExport;
use App\Http\Controllers\Controller;
use App\Models\Procurement\DepartmentNeed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ConsolidatedDashboardController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', DepartmentNeed::class);

        // Normalize inputs
        $branchInput = trim((string)$request->input('branch', ''));
        $departmentInput = trim((string)$request->input('department', ''));
        $yearInput = trim((string)$request->input('year', ''));

        $branch = $branchInput === '' ? null : $branchInput;
        $department = $departmentInput === '' ? null : $departmentInput;
        $year = (strcasecmp($yearInput, 'All Years') === 0 || $yearInput === '') ? null : $yearInput;

        $query = DepartmentNeed::with(['item', 'branch', 'department', 'creator']);

        // Status filtering
        $statusInput = trim((string)$request->input('status', ''));
        $status = $statusInput === '' ? null : $statusInput;
        if ($status) {
            $map = [
                'approved' => \App\Enums\Procurement\DepartmentNeedsEnum::Approved->value,
                'rejected' => \App\Enums\Procurement\DepartmentNeedsEnum::Rejected->value,
                'pending' => \App\Enums\Procurement\DepartmentNeedsEnum::Pending->value,
            ];
            $lower = strtolower($status);
            if (isset($map[$lower])) {
                $query->where('Status', $map[$lower]);
            } else {
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
            $query->whereYear('RequestedDate', $year);
        }

        $needs = $query->orderByDesc('CreatedOn')->get();

        // Load filter dropdown options
        $branches = \App\Models\Core\Branch::orderBy('Name')->pluck('Name', 'Id');
        $departments = \App\Models\HRM\Department::orderBy('Name')->pluck('Name', 'Id');

        $yearsCollection = DepartmentNeed::query()
            ->selectRaw('DISTINCT YEAR(RequestedDate) as yr')
            ->whereNotNull('RequestedDate')
            ->orderBy('yr')
            ->pluck('yr');
        $years = $yearsCollection->toArray();

        // Status dropdown options
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
        try {
            Log::info("Fetching need details for NeedID: {$needId}");

            // Find the need with relationships
            $need = DepartmentNeed::with(['item', 'branch', 'department', 'creator'])
                ->where('NeedID', $needId)
                ->first();

            if (! $need) {
                Log::warning("Need not found: {$needId}");

                return response()->json(['error' => 'Need not found'], 404);
            }

            // Check authorization
            $this->authorize('view', $need);

            // Get status label safely
            $statusLabel = 'N/A';

            try {
                if (is_object($need->Status) && method_exists($need->Status, 'label')) {
                    $statusLabel = $need->Status->label();
                } elseif ($need->Status instanceof \UnitEnum) {
                    $statusLabel = $need->Status->name;
                } elseif (is_string($need->Status)) {
                    // Try to map status value to enum
                    $statusMap = [
                        'a' => 'Approved',
                        'r' => 'Rejected',
                        'p' => 'Pending',
                    ];
                    $statusLabel = $statusMap[strtolower($need->Status)] ?? $need->Status;
                }
            } catch (\Exception $e) {
                Log::warning("Error getting status label: " . $e->getMessage());
                $statusLabel = 'Unknown';
            }

            // Format date properly
            $requestedDate = null;

            try {
                if ($need->RequestedDate) {
                    $requestedDate = \Carbon\Carbon::parse($need->RequestedDate)->format('Y-m-d');
                }
            } catch (\Exception $e) {
                Log::warning("Error parsing date: " . $e->getMessage());
            }

            // Calculate estimated cost safely
            $estimatedCost = 0;

            try {
                $qty = floatval($need->RequestedQty ?? 0);
                $unitCost = floatval($need->EstimatedUnitCost ?? 0);
                $estimatedCost = $qty * $unitCost;
            } catch (\Exception $e) {
                Log::warning("Error calculating cost: " . $e->getMessage());
            }

            $response = [
                'NeedID' => $need->NeedID,
                'ItemName' => $need->item->ItemName ?? 'N/A',
                'BranchName' => $need->branch?->Name ?? 'N/A',
                'DepartmentName' => $need->department?->Name ?? 'N/A',
                'RequestedQty' => $need->RequestedQty ?? 0,
                'EstimatedCost' => number_format($estimatedCost, 2),
                'RequestedDate' => $requestedDate,
                'Status' => $statusLabel,
                'CreatedBy' => $need->creator?->Name ?? $need->CreatedByName ?? 'N/A',
            ];

            Log::info("Successfully fetched need details", $response);

            return response()->json($response);
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::error("Authorization failed for need {$needId}: " . $e->getMessage());

            return response()->json(['error' => 'Unauthorized access'], 403);
        } catch (\Exception $e) {
            Log::error("Error fetching need details for {$needId}: " . $e->getMessage());
            Log::error($e->getTraceAsString());

            return response()->json([
                'error' => 'Failed to load data',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new NeedsExport($request), 'consolidated_needs.xlsx');
    }

    public function create()
    {
        $this->authorize('create', DepartmentNeed::class);

        $branches = DepartmentNeed::distinct()->pluck('branch');
        $departments = DepartmentNeed::distinct()->pluck('department');

        return view('', compact('branches', 'departments'));
    }
}
