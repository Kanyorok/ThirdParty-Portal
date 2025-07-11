<?php

namespace App\Http\Controllers\insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InsuranceReferralController extends Controller
{
    /**
     * Show referral form.
     */



    public function create()
    {
        return view('insurance.referrals.create');
    }

    /**
     * Store new referral.
     */
    public function store(Request $request)
    {
        DB::table('t_InsuranceReferrals')->insert([
            'ClientName' => $request->ClientName,
            'ClientPhone' => $request->ClientPhone,
            'ClientEmail' => $request->ClientEmail,
            'ClientID' => $request->ClientID,
            'BranchID' => $request->BranchID,
            'ReferringOfficerID' => Auth::id(),
            'BankProduct' => $request->BankProduct,
            'SuggestedInsuranceType' => $request->SuggestedInsuranceType,
            'Notes' => $request->Notes,
            'CreatedBy' => Auth::id(),
            'CreatedAt' => now(),
        ]);

        return redirect()->route('insurance.referrals.index')->with('success', 'Referral submitted.');
    }

    /**
     * List referrals submitted by logged-in user.
     */
    public function index()
    {
        $referrals = DB::table('t_InsuranceReferrals')
            ->where('CreatedBy', Auth::id())
            ->orderByDesc('CreatedAt')
            ->get();

        return view('insurance.referrals.index', compact('referrals'));
    }

    /**
     * Show referral status / detail view.
     */
    public function show($id)
    {
        $referral = DB::table('t_InsuranceReferrals')->find($id);
        return view('insurance.referrals.show', compact('referral'));
    }

    /**
     * Show policies linked to referred clients.
     */
    public function clientPolicies()
    {
        $policies = DB::table('t_InsurancePolicies as p')
            ->join('t_InsuranceReferrals as r', 'p.ReferralID', '=', 'r.Id')
            ->where('r.CreatedBy', Auth::id())
            ->select('p.*', 'r.ClientName')
            ->orderByDesc('p.IssuedOn')
            ->get();

        return view('insurance.referrals.policies', compact('policies'));
    }

    public function commissions()
{
    $commissions = DB::table('t_Commissions')
        ->where('ReferringOfficerID', Auth::id())
        ->orderByDesc('EarnedDate')
        ->get();

    return view('insurance.referrals.commissions', compact('commissions'));
}
}