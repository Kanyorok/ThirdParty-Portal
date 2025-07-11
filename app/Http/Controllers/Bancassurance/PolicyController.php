<?php

namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolicyController extends Controller
{
    //
public function create(Request $request)
{
    $referral = null;
    $prefilled = [];

    // 1. Auto-prefill from referral_id in URL (if present)
    if ($request->filled('referral_id')) {
        $referral = DB::table('t_BancassuranceReferrals')->where('Id', $request->referral_id)->first();

        if ($referral) {
            $prefilled = [
                'CustomerID' => $referral->CustomerID,
                'ProductID' => $referral->ProductID,
                'InsurerID' => $referral->PreferredInsurerID
            ];
        }
    }

    // 2. Load data for form dropdowns
    $customers = DB::table('t_BancassuranceCustomers')->get();
    $products = DB::table('t_InsuranceProducts')->get();
    $insurers = DB::table('t_InsuranceProviders')->get();

    // 3. OPTIONAL: Allow manual selection of referrals
    $referrals = DB::table('t_BancassuranceReferrals')
        ->where('Status', '!=', 'Converted')
        ->get();

    return view('bancassurance.policies.create', compact(
        'customers', 'products', 'insurers', 'referral', 'prefilled', 'referrals'
    ));
}


public function store(Request $request)
{
    $request->validate([
        'CustomerID' => 'required',
        'ProductID' => 'required',
        'SumAssured' => 'required|numeric',
        'PremiumAmount' => 'required|numeric',
        'PaymentFrequency' => 'required',
        'PolicyStartDate' => 'required|date',
        'PolicyEndDate' => 'required|date',
    ]);

    DB::table('t_BancassurancePolicies')->insert([
        'ReferralID' => $request->ReferralID, // new field
        'CustomerID' => $request->CustomerID,
        'ProductID' => $request->ProductID,
        'InsurerID' => $request->InsurerID,
        'PolicyNumber' => $request->PolicyNumber,
        'SumAssured' => $request->SumAssured,
        'PremiumAmount' => $request->PremiumAmount,
        'PaymentFrequency' => $request->PaymentFrequency,
        'PolicyStartDate' => $request->PolicyStartDate,
        'PolicyEndDate' => $request->PolicyEndDate,
        'Status' => 'Proposal',
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    // Optional: Update referral status
    if ($request->filled('ReferralID')) {
        DB::table('t_BancassuranceReferrals')->where('Id', $request->ReferralID)->update([
            'Status' => 'Converted',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now()
        ]);
    }

    return redirect()->route('bancassurance.policies.index')->with('success', 'Policy proposal submitted.');
}
public function index(Request $request)
{
    $query = DB::table('t_BancassurancePolicies as p')
        ->leftJoin('t_BancassuranceCustomers as c', 'p.CustomerID', '=', 'c.Id')
        ->leftJoin('t_InsuranceProducts as pr', 'p.ProductID', '=', 'pr.Id')
        ->leftJoin('t_InsuranceProviders as i', 'p.InsurerID', '=', 'i.Id')
        ->select(
            'p.*',
            DB::raw("CONCAT(c.FullName, ' (', c.NationalID, ')') as CustomerName"),
            'pr.Name as ProductName',
            'i.Name as InsurerName'
        );

    // ✅ Apply filters
    if ($request->filled('status')) {
        $query->where('p.Status', $request->status);
    }

    if ($request->filled('from') && $request->filled('to')) {
        $query->whereBetween('p.PolicyStartDate', [$request->from, $request->to]);
    }

    if ($request->filled('customer')) {
        $query->where('c.FullName', 'like', '%' . $request->customer . '%');
    }

    $policies = $query->orderByDesc('p.Id')->get();

    return view('bancassurance.policies.index', compact('policies'));
}


}
