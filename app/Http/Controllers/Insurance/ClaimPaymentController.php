<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClaimPaymentController extends Controller
{
    //
public function create()
{
$unpaidClaims = DB::table('t_BancassuranceClaimApprovals as a')
    ->join('t_BancassuranceClaims as c', 'a.ClaimID', '=', 'c.Id')
    ->join('t_BancassurancePolicies as p', 'c.PolicyID', '=', 'p.Id')
    ->join('t_BancassuranceCustomers as cu', 'p.CustomerID', '=', 'cu.Id')
    ->leftJoin('t_BancassuranceClaimPayments as pay', 'a.ClaimID', '=', 'pay.ClaimID')
    ->whereNull('pay.ClaimID')
    ->select(
        'a.ClaimID as Id',
        'a.ApprovalAmount as ApprovedAmount',
        'c.ClaimType',
        'p.PolicyNumber',
        'cu.FullName as CustomerName'
    )
    ->get();

    return view('bancassurance.claims.payments.create', compact('unpaidClaims'));
}

public function index()
{
$payments = DB::table('t_BancassuranceClaimPayments as p')
    ->leftJoin('t_BancassuranceClaims as c', 'p.ClaimID', '=', 'c.Id')
    ->leftJoin('t_BancassurancePolicies as pol', 'c.PolicyID', '=', 'pol.Id')
    ->leftJoin('t_BancassuranceCustomers as cust', 'pol.CustomerID', '=', 'cust.Id')
    ->select(
        'p.*',
        'pol.PolicyNumber',
        'cust.FullName as CustomerName',
        'c.ClaimType',
        DB::raw("FORMAT(p.AmountPaid, 'N2') as FormattedAmount")
    )
    ->orderByDesc('p.Id')
    ->get();

    return view('bancassurance.claims.payments.index', compact('payments'));
}

public function store(Request $request)
{
    $request->validate([
        'ClaimID' => 'required|exists:t_BancassuranceClaimApprovals,ClaimID',
        'AmountPaid' => 'required|numeric|min:0',
        'PaymentDate' => 'required|date',
        'PaymentMode' => 'required|string|max:50',
    ]);

    // Prevent duplicate payment
    $alreadyPaid = DB::table('t_BancassuranceClaimPayments')
        ->where('ClaimID', $request->ClaimID)
        ->exists();

    if ($alreadyPaid) {
        return redirect()->back()->withErrors(['ClaimID' => 'This claim has already been paid.']);
    }

    DB::table('t_BancassuranceClaimPayments')->insert([
        'ClaimID' => $request->ClaimID,
        'AmountPaid' => $request->AmountPaid,
        'PaymentDate' => $request->PaymentDate,
        'PaymentMode' => $request->PaymentMode,
        'PaidBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    // Optionally update status of claim to "Paid"
    DB::table('t_BancassuranceClaims')
        ->where('Id', $request->ClaimID)
        ->update([
            'Status' => 'Paid',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

    return redirect()->route('bancassurance.claims.payments.index')
        ->with('success', 'Payment processed successfully.');
}

}
