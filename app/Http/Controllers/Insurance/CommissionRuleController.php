<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommissionRuleController extends Controller
{
    //
public function create()
{
    $policyTypes = DB::table('t_CodeDetails')
        ->where('CodeID', 'POLICY_TYPE')
        ->pluck('Description', 'Id');

    $providers = DB::table('t_InsuranceProviders')
        ->where('IsActive', 1)
        ->pluck('Name', 'Id');

    return view('bancassurance.commissions.rules.create', compact('policyTypes', 'providers'));
}

public function store(Request $request)
{
    $request->validate([
        'RuleName' => 'required|string|max:100',
        'PolicyTypeID' => 'nullable|integer',
        'ProductID' => 'nullable|integer',
        'CommissionRate' => 'nullable|numeric|min:0|max:100',
        'FixedAmount' => 'nullable|numeric|min:0',
        'AppliesTo' => 'required|in:Staff,Partner,Both',
    ]);

    DB::table('t_BancassuranceCommissionRules')->insert([
        'RuleName' => $request->RuleName,
        'PolicyTypeID' => $request->PolicyTypeID,
        'ProductID' => $request->ProductID,
        'CommissionRate' => $request->CommissionRate,
        'FixedAmount' => $request->FixedAmount,
        'AppliesTo' => $request->AppliesTo,
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
        'IsActive' => 1,
    ]);

    return redirect()->route('commissions.rules.index')->with('success', 'Commission rule created.');
}
public function index()
{
$rules = DB::table('t_BancassuranceCommissionRules as r')
    ->leftJoin('t_CodeDetails as pt', function ($join) {
        $join->on('r.PolicyTypeID', '=', 'pt.Id')
             ->where('pt.CodeID', '=', 'POLICY_TYPE'); })
    ->leftJoin('t_InsuranceProviders as p', 'r.InsuranceProviderID', '=', 'p.Id')
    ->select('r.*', 'pt.Description as PolicyType', 'p.Name as InsuranceProvider')
    ->orderByDesc('r.Id')
    ->get();


    return view('bancassurance.commissions.rules.index', compact('rules'));
}
}
