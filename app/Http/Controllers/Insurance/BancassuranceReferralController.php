<?php

namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BancassuranceReferralController extends Controller
{
    //
public function create()
{
    return view('bancassurance.referrals.create');
}

public function store(Request $request)
{
    DB::table('t_BancassuranceReferrals')->insert([
        'ClientName' => $request->ClientName,
        'ClientIDNumber' => $request->ClientIDNumber,
        'ClientPhone' => $request->ClientPhone,
        'ClientEmail' => $request->ClientEmail,
        'ProductID' => $request->ProductID,
        'PreferredInsurerID' => $request->PreferredInsurerID,
        'Remarks' => $request->Remarks,
        'ReferralDate' => now(),
        'ReferredBy' => auth()->id(),
        'BranchID' => session('LoginBranchId'), // or from user profile
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    return redirect()->route('bancassurance.referrals.index')->with('success', 'Referral submitted!');
}

public function index()
{
    $referrals = DB::table('t_BancassuranceReferrals as r')
        ->leftJoin('t_InsuranceProducts as p', 'r.ProductID', '=', 'p.Id')
        ->leftJoin('t_InsuranceProviders as i', 'r.PreferredInsurerID', '=', 'i.Id')
        ->leftJoin('t_Employees as e', 'r.AssignedTo', '=', 'e.Id')
        ->where('r.ReferredBy', auth()->id()) // Filter for logged-in staff
        ->select(
            'r.*',
            'p.Name as ProductName',
            'i.Name as InsurerName',
            DB::raw("CONCAT(e.FirstName, ' ', e.LastName) as AssignedToName")
        )
        ->orderByDesc('r.Id')
        ->get();

    return view('bancassurance.referrals.index', compact('referrals'));
}

public function assignList()
{
    $referrals = DB::table('t_BancassuranceReferrals as r')
        ->leftJoin('t_InsuranceProducts as p', 'r.ProductID', '=', 'p.Id')
        ->whereNull('r.AssignedTo')
        ->where('r.Status', 'Pending')
        ->select('r.*', 'p.Name as ProductName')
        ->orderByDesc('r.Id')
        ->get();

    $employees = DB::table('t_Employees')
        ->where('DeletedOn', NULL)
        ->select('Id', 'FirstName', 'LastName')
        ->get();

    return view('bancassurance.referrals.assign', compact('referrals', 'employees'));
}

public function assign(Request $request, $id)
{
    DB::table('t_BancassuranceReferrals')
        ->where('Id', $id)
        ->update([
            'AssignedTo' => $request->AssignedTo,
            'Status' => 'Assigned',
            'UpdatedAt' => now(),
            'UpdatedBy' => auth()->id()
        ]);

    return redirect()->back()->with('success', 'Referral assigned successfully.');
}

public function performanceView()
{
    $performance = DB::table('t_BancassuranceReferrals as r')
        ->join('t_Employees as e', 'r.ReferredBy', '=', 'e.Id')
        ->join('t_Branches as b', 'e.BranchId', '=', 'b.Id') // FIXED: 'BranchId' in t_Employees
        ->select(
            DB::raw("CONCAT(e.FirstName, ' ', e.LastName) AS StaffName"),
            'b.Name as BranchName',
            DB::raw("COUNT(r.Id) AS Total"),
            DB::raw("SUM(CASE WHEN r.Status = 'Converted' THEN 1 ELSE 0 END) AS Converted"),
            DB::raw("SUM(CASE WHEN r.Status = 'Pending' THEN 1 ELSE 0 END) AS Pending")
        )
        ->groupBy('e.Id', 'e.FirstName', 'e.LastName', 'b.Name')
        ->orderByDesc('Total')
        ->get();

    return view('bancassurance.referrals.performance', compact('performance'));
}



}
