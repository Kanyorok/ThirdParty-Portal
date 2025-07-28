<?php

namespace App\Http\Controllers\insurance;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PolicyCancellationController extends Controller
{
    public function create()
    {
        $policies = DB::table('t_InsurancePolicies')
            ->where('Status', 'Active')
            ->get();

        return view('insurance.policymanagement.cancellations.create', compact('policies'));
    }

    public function store(Request $request)
    {
        DB::table('t_PolicyCancellations')->insert([
            'PolicyID' => $request->PolicyID,
            'ReasonCode' => $request->ReasonCode,
            'Notes' => $request->Notes,
            'CreatedBy' => Auth::id(),
            'CreatedAt' => now(),
            'Status' => 'Pending'
        ]);

        return redirect()->route('insurance.policymanagement.policies.index')->with('success', 'Cancellation request submitted.');
    }
}
