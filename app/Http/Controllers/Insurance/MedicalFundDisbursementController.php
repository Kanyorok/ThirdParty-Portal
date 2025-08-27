<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundDisbursement;
use App\Models\Insurance\MedicalFundBeneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalFundDisbursementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    // /bancassurance/medical-funds/{medical_fund}/disbursements
    public function index(\App\Models\Insurance\MedicalFund $medical_fund, \Illuminate\Http\Request $request)
    {
        $q = \App\Models\Insurance\MedicalFundDisbursement::where('FundID',$medical_fund->ID);

        $contributor = null;
        if ($cid = (int)$request->query('contributor')) {
            $q->where('ContributorID', $cid);
            $contributor = \App\Models\Insurance\MedicalFundContributor::where('FundID',$medical_fund->ID)->find($cid);
        }

        $total = (float)$q->clone()->sum('Amount');
        $disbursements = $q->orderByDesc('DisbursementDate')->paginate(20)->appends($request->query());

        return view('bancassurance.medical_fund_disbursements.index', compact('medical_fund','disbursements','total','contributor'));
    }

public function create(\App\Models\Insurance\MedicalFund $medical_fund, \Illuminate\Http\Request $request)
{
    // always initialize the vars you compact()
    $contributor   = null;
    $contributors  = collect();
    $beneficiaries = collect();
    $coverages     = collect();

    if ($cid = (int)$request->query('contributor')) {
        $contributor = \App\Models\Insurance\MedicalFundContributor::where('FundID', $medical_fund->ID)->find($cid);

        if ($contributor) {
            // active beneficiaries for this contributor
            $beneficiaries = $contributor->beneficiaries()
                ->where('IsActive', 1)
                ->orderBy('FullName')
                ->get(['ID','FullName']);

            // coverages available via the contributor's subscribed packages
            // (unique by Coverage ID)
            $coverages = $contributor->packages()
                ->with('coverages')
                ->get()
                ->flatMap->coverages
                ->unique('ID')
                ->values();
        }
    } else {
        // fund-wide: provide a contributor dropdown
        $contributors = \App\Models\Insurance\MedicalFundContributor::where('FundID', $medical_fund->ID)
            ->orderBy('FullName')
            ->get(['ID','FullName']);
    }

    return view(
        'bancassurance.medical_fund_disbursements.create',
        compact('medical_fund', 'contributor', 'contributors', 'beneficiaries', 'coverages')
    );
}

public function store(\App\Models\Insurance\MedicalFund $medical_fund, \Illuminate\Http\Request $request)
{
    $data = $request->validate([
        'DisbursementDate' => ['required','date'],
        'Amount'           => ['required','numeric','min:0.01'],
        'ContributorID'    => ['required','integer'],
        'BeneficiaryID'    => ['required','integer'],
        'CoverageID'       => ['required','integer'],
        'Purpose'          => ['nullable','string','max:500'],
    ]);

    $contributor = \App\Models\Insurance\MedicalFundContributor::where('FundID',$medical_fund->ID)
        ->findOrFail($data['ContributorID']);

    // (Optional) ensure beneficiary belongs to contributor
    \App\Models\Insurance\MedicalFundBeneficiary::where('ContributorID',$contributor->ID)
        ->findOrFail($data['BeneficiaryID']);

    // (Optional) ensure coverage is allowed by contributor's packages
    // (Use the validation we added previously.)

    $data['FundID'] = $medical_fund->ID;

    \App\Models\Insurance\MedicalFundDisbursement::create($data);

    return redirect()
        ->route('bancassurance.medicalfunds.disbursements.index', $medical_fund->ID)
        ->with('success','Disbursement recorded.')
        ->with('filter_contributor', $contributor->ID);
}
    public function edit(MedicalFundDisbursement $disbursement)
    {
        $medical_fund = $disbursement->fund;
        $beneficiaries = $medical_fund->beneficiaries()->where('IsActive',1)->orderBy('FullName')->get(['ID','FullName']);

        return view('bancassurance.medical_fund_disbursements.edit', compact('disbursement','medical_fund','beneficiaries'));
    }

    public function update(Request $request, MedicalFundDisbursement $disbursement)
    {
        $data = $request->validate([
            'BeneficiaryID'     => ['required','integer'],
            'DisbursementDate'  => ['required','date'],
            'Amount'            => ['required','numeric','min:0.01'],
            'Purpose'           => ['nullable','string','max:500'],
        ]);

        $disbursement->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', $disbursement->FundID)
            ->with('success','Disbursement updated.');
    }

    public function destroy(MedicalFundDisbursement $disbursement)
    {
        $fundId = $disbursement->FundID;
        $disbursement->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', $fundId)
            ->with('success','Disbursement deleted.');
    }

    public function remainingLimit(\Illuminate\Http\Request $request, \App\Models\Insurance\MedicalFundContributor $contributor)
{
    $coverageId   = (int)$request->query('coverage_id');
    $beneficiaryId= $request->query('beneficiary_id') ? (int)$request->query('beneficiary_id') : null;
    $onDate       = $request->query('on_date') ? \Carbon\Carbon::parse($request->query('on_date')) : now();

    // load packages with coverages
    $contributor->load(['packages.coverages']);

    // Find coverage config from any active package
    $allowed = $contributor->packages->flatMap(fn($p)=> $p->coverages)->keyBy('ID');
    if (!$allowed->has($coverageId)) {
        return response()->json([
            'ok'=>false, 'message'=>'Coverage not available for this contributor.'
        ], 422);
    }

    $cov = $allowed->get($coverageId);
    $pivot = $cov->pivot; // AnnualLimit, PerVisitLimit, WaitingPeriodDays, Scope

    $subscribedOn = optional(
        $contributor->packages()->where('t_MedicalFundPackages.ID',$cov->ID)->first()
    )->pivot?->SubscribedOn;

    // waiting period check
    $waitingOk = true;
    $waitingMsg = null;
    if ($pivot->WaitingPeriodDays) {
        $wpEnd = \Carbon\Carbon::parse($subscribedOn ?? $contributor->CreatedOn)->addDays($pivot->WaitingPeriodDays);
        if ($onDate->lt($wpEnd)) {
            $waitingOk = false;
            $waitingMsg = 'Waiting period not satisfied until '.$wpEnd->toDateString().'.';
        }
    }

    // Calculate used YTD
    $yearStart = $onDate->copy()->startOfYear();
    $yearEnd   = $onDate->copy()->endOfYear();

    $q = \App\Models\Insurance\MedicalFundDisbursement::query()
        ->where('ContributorID', $contributor->ID)
        ->where('CoverageID', $coverageId)
        ->whereBetween('DisbursementDate', [$yearStart, $yearEnd]);

    if (($pivot->Scope ?? 'PerBeneficiary') === 'PerBeneficiary' && $beneficiaryId) {
        $q->where('BeneficiaryID', $beneficiaryId);
    }

    $used = (float)$q->sum('Amount');
    $annual = (float)($pivot->AnnualLimit ?? 0);
    $remaining = $annual ? max(0, $annual - $used) : null;

    return response()->json([
        'ok' => true,
        'scope' => $pivot->Scope ?? 'PerBeneficiary',
        'annual_limit' => $annual,
        'used_ytd' => $used,
        'remaining' => $remaining,
        'per_visit_limit' => (float)($pivot->PerVisitLimit ?? 0),
        'waiting_ok' => $waitingOk,
        'waiting_message' => $waitingMsg,
        'subscribed_on' => $subscribedOn,
    ]);
}

public function options(\App\Models\Insurance\MedicalFund $medical_fund, \App\Models\Insurance\MedicalFundContributor $contributor)
{
    // Ensure contributor belongs to this fund
    abort_unless((int)$contributor->FundID === (int)$medical_fund->ID, 404);

    // Beneficiaries
    $beneficiaries = $contributor->beneficiaries()
        ->where('IsActive', 1)
        ->orderBy('FullName')
        ->get(['ID','FullName']);

    // Coverages via packages
    $contributor->load(['packages.coverages']);
    $coverages = $contributor->packages
        ->flatMap->coverages
        ->unique('ID')
        ->values()
        ->map(function($cov){
            return [
                'ID'   => $cov->ID,
                'Name' => $cov->Name,
                'AnnualLimit'       => $cov->pivot->AnnualLimit,
                'PerVisitLimit'     => $cov->pivot->PerVisitLimit,
                'WaitingPeriodDays' => $cov->pivot->WaitingPeriodDays,
                'Scope'             => $cov->pivot->Scope ?? 'PerBeneficiary',
            ];
        });

    return response()->json([
        'ok' => true,
        'beneficiaries' => $beneficiaries,
        'coverages'     => $coverages,
    ]);
}


}
