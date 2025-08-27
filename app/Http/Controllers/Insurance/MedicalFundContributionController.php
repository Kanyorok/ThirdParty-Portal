<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContribution;
use Illuminate\Http\Request;

class MedicalFundContributionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // /bancassurance/medical-funds/{medical_fund}/contributions
public function index(\App\Models\Insurance\MedicalFund $medical_fund, \Illuminate\Http\Request $request)
{
    $q = $medical_fund->contributions()->newQuery(); // if you have relation; else base query with FundID

    $contributor = null;
    if ($cid = (int)$request->query('contributor')) {
        $q->where('ContributorID', $cid);
        $contributor = \App\Models\Insurance\MedicalFundContributor::where('FundID',$medical_fund->ID)->find($cid);
    }

    // other filters (date range, type, etc.) go here…

    $total = (float) $q->clone()->sum('Amount');
    $contributions = $q->orderByDesc('ContributionDate')->paginate(20)->appends($request->query());

    return view('bancassurance.medical_fund_contributions.index', compact('medical_fund','contributions','total','contributor'));
}

public function create(\App\Models\Insurance\MedicalFund $medical_fund, \Illuminate\Http\Request $request)
{
    $contributor = null;
    if ($cid = (int)$request->query('contributor')) {
        $contributor = \App\Models\Insurance\MedicalFundContributor::where('FundID',$medical_fund->ID)->find($cid);
    }

    // If not coming from a contributor, you may pass a list to choose from
    $contributors = $contributor
        ? collect()
        : \App\Models\Insurance\MedicalFundContributor::where('FundID',$medical_fund->ID)->orderBy('FullName')->get(['ID','FullName']);

    return view('bancassurance.medical_fund_contributions.create', compact('medical_fund','contributor','contributors'));
}
public function store(\App\Models\Insurance\MedicalFund $medical_fund, \Illuminate\Http\Request $request)
{
    $data = $request->validate([
        'ContributionDate' => ['required','date'],
        'Amount'           => ['required','numeric','min:0.01'],
        'Notes'            => ['nullable','string','max:500'],
        'ContributorID'    => ['required','integer'],
        'ContributorType'  => ['nullable','in:Employee,Employer'], // if you use it
    ]);

    $contributor = \App\Models\Insurance\MedicalFundContributor::where('FundID',$medical_fund->ID)
        ->findOrFail($data['ContributorID']); // ✅ guarantees the contributor is from this fund

    $data['FundID'] = $medical_fund->ID;

    \App\Models\Insurance\MedicalFundContribution::create($data);

    return redirect()
        ->route('bancassurance.medicalfunds.contributions.index', $medical_fund->ID)
        ->with('success','Contribution recorded.')
        ->with('filter_contributor', $contributor->ID);
}

    public function edit(MedicalFundContribution $contribution)
    {
        $medical_fund = $contribution->fund;
        return view('bancassurance.medical_fund_contributions.edit', compact('contribution','medical_fund'));
    }

    public function update(Request $request, MedicalFundContribution $contribution)
    {
        $data = $request->validate([
            'ContributorType'  => ['required','string','max:50'],
            'ContributorID'    => ['nullable','integer'],
            'Amount'           => ['required','numeric','min:0.01'],
            'ContributionDate' => ['required','date'],
            'Notes'            => ['nullable','string','max:500'],
        ]);

        $contribution->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $contribution->FundID)
            ->with('success','Contribution updated.');
    }

    public function destroy(MedicalFundContribution $contribution)
    {
        $fundId = $contribution->FundID;
        $contribution->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $fundId)
            ->with('success','Contribution deleted.');
    }
}
