<?php

namespace App\Http\Controllers\insurance;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PolicyEndorsementController extends Controller
{
    public function create()
    {
        $policies = DB::table('t_InsurancePolicies')
            ->where('Status', 'Active')
            ->get();

        return view('insurance.policymanagement.endorsements.create', compact('policies'));
    }

    public function store(Request $request)
    {
        DB::table('t_PolicyEndorsements')->insert([
            'PolicyID' => $request->PolicyID,
            'Type' => $request->Type,
            'OldSumAssured' => $request->OldSumAssured,
            'NewSumAssured' => $request->NewSumAssured,
            'OldPremium' => $request->OldPremium,
            'NewPremium' => $request->NewPremium,
            'Reason' => $request->Reason,
            'CreatedBy' => Auth::id(),
            'CreatedAt' => now(),
        ]);

        return redirect()->route('insurance.policymanagement.policies.index')->with('success', 'Endorsement submitted.');
    }
}

