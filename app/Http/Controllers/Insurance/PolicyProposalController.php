<?php

namespace App\Http\Controllers\insurance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PolicyProposalController extends Controller
{
    public function create()
    {
        $referrals = DB::table('t_InsuranceReferrals')
            ->where('Status', 'Pending')
            ->get();

        $products = DB::table('t_InsuranceProducts')
            ->where('Status', 'Active')
            ->get();

        return view('insurance.policymanagement.policy-proposals.create', compact('referrals', 'products'));
    }

    public function store(Request $request)
    {
        DB::table('t_PolicyProposals')->insert([
            'ReferralID' => $request->ReferralID,
            'ProductID' => $request->ProductID,
            'SumAssured' => $request->SumAssured,
            'Premium' => $request->Premium, // calculated in real system
            'StartDate' => $request->StartDate,
            'EndDate' => $request->EndDate,
            'Notes' => $request->Notes,
            'Status' => 'Submitted',
            'CreatedBy' => Auth::id(),
            'CreatedAt' => now(),
        ]);

        return redirect()->route('insurance.policymanagement.policy-proposals.create')->with('success', 'Proposal submitted.');
    }
}

