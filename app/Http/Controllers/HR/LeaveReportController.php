<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\LeaveRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Key Metrics (Cards)
        $today = Carbon::today();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Employees on Leave Today
        $onLeaveToday = LeaveRequest::where('Status', 'Approved')
            ->whereDate('StartDate', '<=', $today)
            ->whereDate('EndDate', '>=', $today)
            ->count();

        // Pending Requests (Total)
        $pendingRequests = LeaveRequest::where('Status', 'Pending')->count();

        // Approved This Month
        $approvedMonth = LeaveRequest::where('Status', 'Approved')
            ->whereBetween('StartDate', [$startOfMonth, $endOfMonth])
            ->count();

        // Rejected This Month
        $rejectedMonth = LeaveRequest::where('Status', 'Rejected')
            ->whereBetween('UpdatedOn', [$startOfMonth, $endOfMonth]) // Assuming UpdatedOn tracks when it was rejected
            ->count();

        // 2. Chart Data: Leave Type Distribution (Yearly)
        $startOfYear = Carbon::now()->startOfYear();
        $typeStats = LeaveRequest::where('Status', 'Approved')
            ->where('StartDate', '>=', $startOfYear)
            ->select('LeaveTypeID', DB::raw('count(*) as total'))
            ->with('type')
            ->groupBy('LeaveTypeID')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => $item->type->Name ?? 'Unknown',
                    'value' => $item->total,
                ];
            });

        // 3. Chart Data: Monthly Trends (Last 6 Months)
        $trendStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = LeaveRequest::where('Status', 'Approved')
                ->whereMonth('StartDate', $month->month)
                ->whereYear('StartDate', $month->year)
                ->count();
            $trendStats[] = [
                'month' => $month->format('M Y'),
                'count' => $count,
            ];
        }

        // 4. Detailed List (DataTable)
        $query = LeaveRequest::with(['employee', 'type', 'reliever'])
            ->orderByDesc('CreatedOn');

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('employee_id')) {
            $query->where('EmployeeID', $request->employee_id);
        }

        $limit = 50;
        $leaves = $query->paginate($limit);

        // Filter options
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName']);

        return view('hr.leave.reports.index', compact(
            'onLeaveToday',
            'pendingRequests',
            'approvedMonth',
            'rejectedMonth',
            'typeStats',
            'trendStats',
            'leaves',
            'employees'
        ));
    }
}
