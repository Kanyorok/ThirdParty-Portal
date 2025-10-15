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
            'package_ids'   => ['nullable','array'],
            'package_ids.*' => ['integer'],
        ]);
        $data['FundID'] = $medical_fund->ID;

        $contributor = MedicalFundContributor::create($data);

        // Sync selected packages (including compulsory hidden inputs)
        $ids = collect($request->input('package_ids', []))
            ->map(fn($v)=>(int)$v)
            ->unique()
            ->values();

        if ($ids->count()) {
            // Choose a primary: keep single selection as primary; multiple -> pick first
            $primaryId = $ids->first();
            $today = \Carbon\Carbon::now()->toDateString();

            $sync = $ids->mapWithKeys(function($pid) use ($primaryId, $today){
                return [ $pid => ['IsActive'=>1,'SubscribedOn'=>$today,'IsPrimary'=> $pid === $primaryId ? 1 : 0] ];
            })->all();

            $contributor->packages()->sync($sync);
        }

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
    $contributor   = MedicalFundContributor::with('fund')->findOrFail($id);
    $medical_fund  = $contributor->fund ?: MedicalFund::find($contributor->FundID);

    return view('bancassurance.medical_fund_contributors.edit', compact('contributor','medical_fund'));
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
            'package_ids'   => ['nullable','array'],
            'package_ids.*' => ['integer'],
        ]);

        $contributor->update($data);

        // Sync selected packages
        $ids = collect($request->input('package_ids', []))
            ->map(fn($v)=>(int)$v)
            ->unique()
            ->values();

        if ($ids->count()) {
            // Preserve existing primary if still selected; otherwise pick first
            $existingPrimary = optional(
                $contributor->packages()->wherePivot('IsPrimary',1)->first()
            )->ID;

            $primaryId = $existingPrimary && $ids->contains($existingPrimary)
                ? $existingPrimary
                : $ids->first();

            // Preserve existing SubscribedOn where present; default to today for new links
            $today = \Carbon\Carbon::now()->toDateString();
            $existing = $contributor->packages()
                ->whereIn('t_MedicalFundPackages.ID', $ids)
                ->get()
                ->mapWithKeys(function($p){
                    return [ (int)$p->ID => optional($p->pivot)->SubscribedOn ];
                });

            $sync = $ids->mapWithKeys(function($pid) use ($primaryId, $today, $existing){
                $subOn = $existing->get((int)$pid) ?: $today;
                return [ $pid => ['IsActive'=>1,'SubscribedOn'=>$subOn,'IsPrimary'=> $pid === $primaryId ? 1 : 0] ];
            })->all();

            $contributor->packages()->sync($sync);
        } else {
            // If nothing selected, detach all
            $contributor->packages()->detach();
        }

        return redirect()
            ->route('bancassurance.contributors.show', $contributor->ID)
            ->with('success','Contributor updated.');
    }

    public function destroy(MedicalFundContributor $contributor)
    {
        $fundId = $contributor->FundID;
        $contributor->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.contributors.index', ['medical_fund' => $fundId])
            ->with('success','Contributor archived.');
    }
}
