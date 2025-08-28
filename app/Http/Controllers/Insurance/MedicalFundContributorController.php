<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContributor;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MedicalFundContributorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // /bancassurance/medicalfunds/{medical_fund}/contributors
    public function index(MedicalFund $medical_fund, Request $request)
    {
        $q = $medical_fund->contributors()->newQuery();

        if ($s = $request->get('search')) {
            $q->where(function($x) use ($s){
                $x->where('FullName','like',"%{$s}%")
                  ->orWhere('ContributorNo','like',"%{$s}%")
                  ->orWhere('Email','like',"%{$s}%")
                  ->orWhere('Phone','like',"%{$s}%");
            });
        }
        if ($status = $request->get('status')) {
            $q->where('Status', $status);
        }

        $q->orderBy('FullName');
        $contributors = $q->paginate(20)->appends($request->query());

        return view('bancassurance.medical_fund_contributors.index', compact('medical_fund','contributors'));
    }

    public function create(MedicalFund $medical_fund)
    {
        return view('bancassurance.medical_fund_contributors.create', compact('medical_fund'));
    }

    public function store(Request $request, MedicalFund $medical_fund)
    {
        $data = $request->validate([
            'ContributorNo' => ['nullable','string','max:50'],
            'FullName'      => ['required','string','max:255'],
            'Email'         => ['nullable','email','max:150'],
            'Phone'         => ['nullable','string','max:50'],
            'EffectiveFrom' => ['nullable','date'],
            'EffectiveTo'   => ['nullable','date','after_or_equal:EffectiveFrom'],
            'Status'        => ['nullable','in:Active,Suspended,Closed'],
            'PartyID'       => ['nullable','integer'],
        ]);
        $data['FundID'] = $medical_fund->ID;

        $contributor = MedicalFundContributor::create($data);

        return redirect()
            ->route('bancassurance.contributors.show', $contributor->ID)
            ->with('success','Contributor created.');
    }

    // shallow routes below
public function show(MedicalFundContributor $contributor)
{
    $contributor->load(['fund','beneficiaries']);

    $totals = [
        'contrib_sum' => $contributor->contributions()->sum('Amount'),
        'disb_sum'    => $contributor->disbursements()->sum('Amount'),
    ];

    // Try DB-managed list; fallback to static list if table absent/empty
    $relationships = collect();
    try {
        if (DB::getSchemaBuilder()->hasTable('t_BeneficiaryRelationships')) {
            $relationships = DB::table('t_BeneficiaryRelationships')
                ->where('IsActive',1)->orderBy('Name')->get(['Code','Name']);
        }
    } catch (\Throwable $e) { /* ignore */ }

    if ($relationships->isEmpty()) {
        $relationships = collect([
            (object)['Code'=>'SELF','Name'=>'Self'],
            (object)['Code'=>'SPOUSE','Name'=>'Spouse'],
            (object)['Code'=>'CHILD','Name'=>'Child'],
            (object)['Code'=>'PARENT','Name'=>'Parent'],
            (object)['Code'=>'GUARDIAN','Name'=>'Guardian'],
            (object)['Code'=>'SIBLING','Name'=>'Sibling'],
            (object)['Code'=>'OTHER','Name'=>'Other'],
        ]);
    }

    return view('bancassurance.medical_fund_contributors.show',
        compact('contributor','totals','relationships'));
}

public function edit($id)
{
    $contributor = MedicalFundContributor::findOrFail($id);
    return view('insurance.medicalfundcontributors.edit', compact('contributor'));
}
    public function update(Request $request, MedicalFundContributor $contributor)
    {
        $data = $request->validate([
            'ContributorNo' => ['nullable','string','max:50'],
            'FullName'      => ['required','string','max:255'],
            'Email'         => ['nullable','email','max:150'],
            'Phone'         => ['nullable','string','max:50'],
            'EffectiveFrom' => ['nullable','date'],
            'EffectiveTo'   => ['nullable','date','after_or_equal:EffectiveFrom'],
            'Status'        => ['required','in:Active,Suspended,Closed'],
            'PartyID'       => ['nullable','integer'],
        ]);

        $contributor->update($data);

        return redirect()
            ->route('bancassurance.contributors.show', $contributor->ID)
            ->with('success','Contributor updated.');
    }

    public function destroy(MedicalFundContributor $contributor)
    {
        $fundId = $contributor->FundID;
        $contributor->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.contributors.index', $fundId)
            ->with('success','Contributor archived.');
    }
}
