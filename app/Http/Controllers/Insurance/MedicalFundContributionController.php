<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContribution;
use App\Models\Insurance\MedicalFundContributor;
use Illuminate\Http\Request;

class MedicalFundContributionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(MedicalFund $medical_fund, Request $request)
    {
        // start a query for contributions and eager-load contributor and type to avoid N+1
        $q = $medical_fund->contributions()->with(['contributor.thirdParty','type'])->newQuery();


        $contributor = null;
        if ($cid = (int) $request->query('contributor')) {
            $q->where('ContributorId', $cid);

            $contributor = $medical_fund->contributors()
                ->find($cid);
        }

        $total = (float) $q->clone()->sum('Amount');
        $contributions = $q->orderByDesc('ContributionDate')
            ->paginate(20)
            ->appends($request->query());

        return view('bancassurance.medical_fund_contributions.index', compact(
            'medical_fund', 'contributions', 'total', 'contributor'
        ));
    }

    public function create(MedicalFund $medical_fund, Request $request)
    {
        $contributor = null;
        if ($cid = (int)$request->query('contributor')) {
            $contributor = MedicalFundContributor::where('FundId',$medical_fund->Id)->find($cid);
        }

        // If not coming from a contributor, you may pass a list to choose from
        $contributors = $contributor
            ? collect()
            : MedicalFundContributor::where('FundId',$medical_fund->Id)->get();

        return view('bancassurance.medical_fund_contributions.create', compact('medical_fund','contributor','contributors'));
    }
    public function store(MedicalFund $medical_fund, Request $request)
    {
        $data = $request->validate([
            'ContributionDate' => ['required','date'],
            'Amount'           => ['required','numeric','min:0.01'],
            'Notes'            => ['nullable','string','max:500'],
            'ContributorId'    => ['required','integer'],
            'ContributorType'  => ['nullable','in:Employee,Employer'], // if you use it
        ]);

        $contributor = MedicalFundContributor::where('FundId',$medical_fund->Id)
            ->findOrFail($data['ContributorId']); // ✅ guarantees the contributor is from this fund

        $data['FundId'] = $medical_fund->Id;

        MedicalFundContribution::create($data);

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $medical_fund->Id)
            ->with('success','Contribution recorded.')
            ->with('filter_contributor', $contributor->Id);
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
            'ContributorId'    => ['nullable','integer'],
            'Amount'           => ['required','numeric','min:0.01'],
            'ContributionDate' => ['required','date'],
            'Notes'            => ['nullable','string','max:500'],
        ]);

        $contribution->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $contribution->FundId)
            ->with('success','Contribution updated.');
    }

    public function destroy(MedicalFundContribution $contribution)
    {
        $fundId = $contribution->FundId;
        $contribution->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.contributions.index', $fundId)
            ->with('success','Contribution deleted.');
    }
}
