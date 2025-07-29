<?php

namespace App\Http\Controllers\Insuarance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CommissionTierController extends Controller
{
    //
public function index($ruleId)
{
    $tiers = DB::table('t_BancassuranceCommissionTiers')
        ->where('RuleID', $ruleId)
        ->orderBy('MinValue')
        ->get();

    return view('bancassurance.commissions.tiers.index', compact('tiers', 'ruleId'));
}

public function store(Request $request, $ruleId)
{
    $request->validate([
        'MinValue' => 'required|numeric|min:0',
        'MaxValue' => 'nullable|numeric|gt:MinValue',
        'CommissionRate' => 'required|numeric|min:0|max:100',
    ]);

    DB::table('t_BancassuranceCommissionTiers')->insert([
        'RuleID' => $ruleId,
        'MinValue' => $request->MinValue,
        'MaxValue' => $request->MaxValue,
        'CommissionRate' => $request->CommissionRate,
        'CreatedAt' => now(),
    ]);

    return redirect()->route('bancassurance.commissions.tiers.index', $ruleId)->with('success', 'Tier added.');
}

}
