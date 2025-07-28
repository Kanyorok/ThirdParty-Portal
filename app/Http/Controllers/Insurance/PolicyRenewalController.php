<?php

namespace App\Http\Controllers\insurance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PolicyRenewalController extends Controller
{
    public function create()
    {
        $policies = DB::table('t_InsurancePolicies')
            ->whereIn('Status', ['Active', 'Expired'])
            ->get();

        return view('insurance.policymanagement.renewals.create', compact('policies'));
    }

    public function store(Request $request)
    {
        DB::table('t_PolicyRenewals')->insert([
            'PolicyID' => $request->PolicyID,
            'NewStartDate' => $request->NewStartDate,
            'NewEndDate' => $request->NewEndDate,
            'NewSumAssured' => $request->NewSumAssured,
            'NewPremium' => $request->NewPremium,
            'Notes' => $request->Notes,
            'CreatedBy' => Auth::id(),
            'CreatedAt' => now()
        ]);

        // Optional: Update policy table with new dates if immediate effect is needed
        return redirect()->route('insurance.policymanagement.policies.index')->with('success', 'Policy renewal submitted.');
    }
}

