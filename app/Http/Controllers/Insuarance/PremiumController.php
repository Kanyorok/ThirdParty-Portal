<?php

namespace App\Http\Controllers\Insuarance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PremiumController extends Controller
{
    //
public function create()
{
    // Fetch all active policies with customer names
    $policies = DB::table('t_BancassurancePolicies as p')
        ->join('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->select('p.Id', 'p.PolicyNumber', 'c.FullName as CustomerName')
        ->where('p.Status', 'ApprovedForIssuance')
        ->orderBy('p.PolicyNumber')
        ->get();

    return view('bancassurance.premiums.create', compact('policies'));
}

public function store(Request $request)
{
    $request->validate([
        'PolicyID' => 'required|exists:t_BancassurancePolicies,Id',
        'PaymentDate' => 'required|date',
        'Amount' => 'required|numeric|min:1',
        'PaymentMode' => 'required|string|max:50',
        'ReferenceNumber' => 'nullable|string|max:100',
        'Notes' => 'nullable|string|max:255',
    ]);

    DB::table('t_BancassurancePremiumPayments')->insert([
        'PolicyID' => $request->PolicyID,
        'PaymentDate' => $request->PaymentDate,
        'Amount' => $request->Amount,
        'PaymentMode' => $request->PaymentMode,
        'ReferenceNumber' => $request->ReferenceNumber,
        'Notes' => $request->Notes,
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    return redirect()->route('bancassurance.premiums.index')
        ->with('success', 'Premium payment recorded successfully.');
}


public function index()
{
    $payments = DB::table('t_BancassurancePremiumPayments as pay')
        ->join('t_BancassurancePolicies as pol', 'pay.PolicyID', '=', 'pol.Id')
        ->join('t_BancassuranceCustomers as cust', 'pol.CustomerID', '=', 'cust.Id')
        ->select(
            'pay.*',
            'pol.PolicyNumber',
            'cust.FullName as CustomerName'
        )
        ->orderByDesc('pay.PaymentDate')
        ->get();

    return view('bancassurance.premiums.index', compact('payments'));
}

public function printReceipt($id)
{
    $payment = DB::table('t_BancassurancePremiumPayments as p')
        ->leftJoin('t_BancassurancePolicies as pol', 'p.PolicyID', '=', 'pol.Id')
        ->leftJoin('t_BancassuranceCustomers as c', 'pol.CustomerID', '=', 'c.Id')
        ->leftJoin('t_Employees as e', 'p.ReceivedBy', '=', 'e.Id')
        ->select(
            'p.*',
            'pol.PolicyNumber',
            'c.FullName as CustomerName',
            DB::raw("CONCAT(e.FirstName, ' ', e.LastName) as ReceivedByName")
        )
        ->where('p.Id', $id)
        ->first();

    return view('bancassurance.premiums.receipt', compact('payment'));
}
}
