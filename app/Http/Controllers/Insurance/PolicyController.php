<?php

namespace App\Http\Controllers\insurance;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class PolicyController extends Controller
{
    public function index()
    {
        // Simplified: Merging both proposals and policies for display
        $policies = DB::table('t_PolicyProposals as pp')
            ->leftJoin('t_InsuranceProducts as prod', 'pp.ProductID', '=', 'prod.Id')
            ->leftJoin('t_InsuranceReferrals as ref', 'pp.ReferralID', '=', 'ref.Id')
            ->select(
                DB::raw("CONCAT('PROP-', pp.Id) as PolicyNo"),
                'ref.ClientName',
                'prod.ProductName',
                'pp.SumAssured',
                'pp.Premium',
                'pp.StartDate',
                'pp.EndDate',
                'pp.Status'
            )
            ->orderByDesc('pp.CreatedAt')
            ->get();

        return view('insurance.policymanagement.policies.index', compact('policies'));
    }
    

public function schedule($id)
{
    $policy = DB::table('t_InsurancePolicies as p')
        ->leftJoin('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
        ->leftJoin('t_PolicyProposals as pr', 'p.ProposalID', '=', 'pr.Id')
        ->select('p.*', 'prod.ProductName', 'pr.Notes')
        ->where('p.Id', $id)
        ->first();

    $pdf = Pdf::loadView('insurance.policymanagement.policies.schedule', compact('policy'));
    return $pdf->download("PolicySchedule_{$policy->PolicyNo}.pdf");
}
}
