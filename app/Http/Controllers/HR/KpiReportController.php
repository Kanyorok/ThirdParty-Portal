<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiAppraisal;
use App\Models\HR\KpiPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiReportController extends Controller
{
    public function index(Request $request)
    {
        $group = $request->get('group', 'employee');
        $periodId = $request->filled('period_id') ? (int)$request->period_id : null;
        $status = $request->get('status', 'Approved');

        $base = DB::table('t_HRKPIAppraisals as a')
            ->join('t_HREmployees as e', 'e.Id', '=', 'a.EmployeeID')
            ->leftJoin('t_Branches as b', 'b.Id', '=', 'e.BranchID')
            ->leftJoin('t_Departments as d', 'd.Id', '=', 'e.DepartmentID')
            ->select([
                'a.Id',
                'a.TotalScore',
                'a.Status',
                'a.PeriodID',
                'e.Id as EmployeeID',
                'e.FirstName',
                'e.LastName',
                'e.EmployeeNo',
                'b.Id as BranchID',
                'b.Name as BranchName',
                'd.Id as DepartmentID',
                'd.Name as DepartmentName',
            ]);

        if ($status) {
            $base->where('a.Status', $status);
        }
        if ($periodId) {
            $base->where('a.PeriodID', $periodId);
        }

        $rows = collect();
        if ($group === 'department') {
            $query = clone $base;
            $rows = $query->selectRaw('DepartmentID, DepartmentName, COUNT(*) as Appraisals, AVG(TotalScore) as AvgScore')
                ->groupBy('DepartmentID', 'DepartmentName')
                ->orderBy('DepartmentName')
                ->get();
        } elseif ($group === 'branch') {
            $query = clone $base;
            $rows = $query->selectRaw('BranchID, BranchName, COUNT(*) as Appraisals, AVG(TotalScore) as AvgScore')
                ->groupBy('BranchID', 'BranchName')
                ->orderBy('BranchName')
                ->get();
        } elseif ($group === 'org') {
            $query = clone $base;
            $rows = $query->selectRaw('COUNT(*) as Appraisals, AVG(TotalScore) as AvgScore')
                ->get();
        } else {
            $query = clone $base;
            $rows = $query->selectRaw('EmployeeID, EmployeeNo, FirstName, LastName, BranchName, DepartmentName, COUNT(*) as Appraisals, AVG(TotalScore) as AvgScore')
                ->groupBy('EmployeeID','EmployeeNo','FirstName','LastName','BranchName','DepartmentName')
                ->orderBy('FirstName')
                ->get();
        }

        $periods = KpiPeriod::where('IsActive', 1)->orderBy('Name')->get(['Id','Name']);

        return view('hr.kpi.reports.index', compact('rows','periods','group','status','periodId'));
    }
}
