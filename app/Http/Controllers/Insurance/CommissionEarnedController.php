<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Insurance\BancassuranceCommissionEarned;
use Illuminate\Support\Facades\DB;

class CommissionEarnedController extends Controller
{
public function index(Request $request)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $query = DB::table('t_BancassuranceCommissionsEarned as e')
        ->leftJoin('t_BancassurancePolicies as pol', 'e.PolicyID', '=', 'pol.Id')
        ->select('e.*', 'pol.PolicyNumber');

    if ($month && $year) {
        $query->whereMonth('e.EarnedDate', $month)
              ->whereYear('e.EarnedDate', $year);
    }

    $earneds = $query->orderByDesc('e.EarnedDate')->get();

    return view('bancassurance.commissions.earned.index', compact('earneds', 'month', 'year',));
}

    public function payForm($id)
    {
        $commission = DB::table('t_BancassuranceCommissionsEarned as e')
            ->leftJoin('t_BancassurancePolicies as p', 'e.PolicyID', '=', 'p.Id')
            ->where('e.Id', $id)
            ->select('e.*', 'p.PolicyNumber')
            ->first();

        return view('bancassurance.commissions.earned.pay', compact('commission'));
    }

    public function storePayout(Request $request, $id)
    {
        $request->validate([
            'PayoutReference' => 'required|string|max:100',
            'PaidAmount' => 'required|numeric|min:0',
            'PaymentDate' => 'required|date',
            'PaymentMode' => 'nullable|string|max:50',
            'Remarks' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $id) {
            DB::table('t_BancassuranceCommissionPayouts')->insert([
                'CommissionEarnedID' => $id,
                'PayoutReference' => $request->PayoutReference,
                'PaidAmount' => $request->PaidAmount,
                'PaymentDate' => $request->PaymentDate,
                'PaymentMode' => $request->PaymentMode,
                'Remarks' => $request->Remarks,
                'PaidBy' => auth()->id(),
                'CreatedAt' => now(),
            ]);

            DB::table('t_BancassuranceCommissionsEarned')->where('Id', $id)->update([
                'Status' => 'Paid',
            ]);
        });

        return redirect()->route('bancassurance.commissions.earned.index')->with('success', 'Commission payout recorded.');
    }

    public function bulkPayout(Request $request)
{
    $month = $request->input('month');
    $year = $request->input('year');

    $earneds = DB::table('t_BancassuranceCommissionsEarned')
        ->whereMonth('EarnedDate', $month)
        ->whereYear('EarnedDate', $year)
        ->where('IsPaid', 0)
        ->get();

    foreach ($earneds as $e) {
        DB::table('t_BancassuranceCommissionPayouts')->insert([
            'CommissionEarnedID' => $e->Id,
            'PaidAmount' => $e->EarnedAmount,
            'PaymentMode' => 'Bulk Batch',
            'PayoutReference' => 'BULK-' . uniqid(),
            'PaymentDate' => now(),
            'Remarks' => 'Auto-paid via bulk payout',
            'CreatedAt' => now(),
            'CreatedBy' => auth()->id(),
        ]);

        DB::table('t_BancassuranceCommissionsEarned')
            ->where('Id', $e->Id)
            ->update(['IsPaid' => 1]);
    }

    return redirect()->route('bancassurance.commissions.earned.index', ['month' => $month, 'year' => $year])
        ->with('success', 'Bulk commission payout completed.');
}

}

