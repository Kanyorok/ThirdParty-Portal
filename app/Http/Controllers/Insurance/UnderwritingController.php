<?php

namespace App\Http\Controllers\insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UnderwritingController extends Controller
{
    /**
     * Show underwriting rules configuration page.
     */
    public function rules()
    {
        $rules = DB::table('t_UnderwritingRules as r')
            ->join('t_InsuranceProducts as p', 'r.ProductID', '=', 'p.Id')
            ->select('r.*', 'p.ProductName')
            ->orderBy('r.CreatedAt', 'desc')
            ->get();

        $products = DB::table('t_InsuranceProducts')->where('Status', 'Active')->get();

        return view('insurance.underwriting.rules.index', compact('rules', 'products'));
    }

    /**
     * Store a new underwriting rule.
     */
    public function storeRule(Request $request)
    {
        $request->validate([
            'RuleType' => 'required|string|max:100',
            'ProductID' => 'required|integer',
            'Criteria' => 'required|string|max:100',
            'Value' => 'required|string|max:255',
            'IsActive' => 'required|boolean',
        ]);

        DB::table('t_UnderwritingRules')->insert([
            'RuleType' => $request->RuleType,
            'ProductID' => $request->ProductID,
            'Criteria' => $request->Criteria,
            'Value' => $request->Value,
            'IsActive' => $request->IsActive,
            'CreatedBy' => Auth::id(),
            'CreatedAt' => now()
        ]);

        return redirect()->route('insurance.underwriting.rules')->with('success', 'Underwriting rule saved.');
    }

    /**
     * Show proposals awaiting underwriting review (stub for 3.2).
     */
    public function reviewList()
    {
        $proposals = DB::table('t_PolicyProposals as p')
            ->join('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
            ->join('t_InsuranceReferrals as r', 'p.ReferralID', '=', 'r.Id')
            ->select('p.*', 'prod.ProductName', 'r.ClientName')
            ->where('p.Status', 'Submitted')
            ->orderByDesc('p.CreatedAt')
            ->get();

        return view('insurance.underwriting.review.index', compact('proposals'));

    }

    /**
     * Show a specific proposal for underwriting review (stub for 3.2).
     */
    public function review($id)
    {
        $proposal = DB::table('t_PolicyProposals as p')
            ->join('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
            ->join('t_InsuranceReferrals as r', 'p.ReferralID', '=', 'r.Id')
            ->select('p.*', 'prod.ProductName', 'r.ClientName', 'r.ClientPhone', 'r.ClientEmail')
            ->where('p.Id', $id)
            ->first();

        if (!$proposal) {
            abort(404, 'Proposal not found.');
        }

        return view('insurance.underwriting.review.show', compact('proposal'));
    }

    /**
     * Submit decision (approve/reject/return) on proposal (stub for 3.5).
     */
    public function submitDecision(Request $request, $id)
    {
        $request->validate([
            'Decision' => 'required|in:Approved,Rejected,Returned',
            'Comments' => 'nullable|string|max:500',
        ]);

        DB::table('t_PolicyProposals')
            ->where('Id', $id)
            ->update([
                'Status' => $request->Decision,
                'UpdatedAt' => now(),
                'UpdatedBy' => Auth::id()
            ]);

        return redirect()->route('insurance.underwriting.proposals.index')->with('success', 'Decision recorded.');
    }

    
}
