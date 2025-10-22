<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContributor;
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
    public function index(MedicalFund $medical_fund, Request $request)
    {
        $q = MedicalFundDisbursement::where('FundId',$medical_fund->Id);

        $contributor = null;
        if ($cid = (int)$request->query('contributor')) {
            $q->where('ContributorId', $cid);
            $contributor = MedicalFundContributor::where('FundId',$medical_fund->Id)->find($cid);
        }

        $total = (float)$q->clone()->sum('Amount');
        $disbursements = $q->orderByDesc('DisbursementDate')->paginate(20)->appends($request->query());

        return view('bancassurance.medical_fund_disbursements.index', compact('medical_fund','disbursements','total','contributor'));
    }

public function create(MedicalFund $medical_fund, Request $request)
{
    // always initialize the vars you compact()
    $contributor   = null;
    $contributors  = collect();
    $beneficiaries = collect();
    $coverages     = collect();

    if ($cid = (int)$request->query('contributor')) {
        $contributor = MedicalFundContributor::where('FundId', $medical_fund->Id)->find($cid);

        if ($contributor) {
            // active beneficiaries for this contributor
            $beneficiaries = $contributor->beneficiaries()
                ->where('IsActive', true)
                ->get();

            // coverages available via the contributor's subscribed packages
            // (unique by Coverage ID)
            $coverages = $contributor->packages()
                ->with('coverages')
                ->get()
                ->flatMap->coverages
                ->unique('Id')
                ->values();
        }
    } else {
        // fund-wide: provide a contributor dropdown
        $contributors = MedicalFundContributor::where('FundId', $medical_fund->Id)
            ->get();
    }

    return view(
        'bancassurance.medical_fund_disbursements.create',
        compact('medical_fund', 'contributor', 'contributors', 'beneficiaries', 'coverages')
    );
}

public function store(MedicalFund $medical_fund, Request $request)
{
    $data = $request->validate([
        'DisbursementDate' => ['required','date'],
        'Amount'           => ['required','numeric','min:0.01'],
        'ContributorId'    => ['required','integer'],
        'BeneficiaryId'    => ['required','integer'],
        'CoverageId'       => ['required','integer'],
        'Purpose'          => ['nullable','string','max:500'],
    ]);

    // Ensure contributor belongs to this fund
    $contributor = MedicalFundContributor::where('FundId', $medical_fund->Id)
        ->findOrFail($data['ContributorId']);

    // Ensure beneficiary belongs to contributor
    MedicalFundBeneficiary::where('ContributorId', $contributor->Id)
        ->findOrFail($data['BeneficiaryId']);

    // Figure out which package (of this contributor) contains the chosen coverage
    $contributor->load(['packages.coverages']);
    $package = $contributor->packages
        ->first(function($p) use ($data) {
            return $p->coverages->firstWhere('Id', (int)$data['CoverageId']);
        });

    $data['PackageId'] = $package?->Id;        // may be null if not found
    $data['FundId']    = $medical_fund->Id;

    MedicalFundDisbursement::create($data);

    return redirect()
        ->route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $medical_fund->Id])
        ->with('success','Disbursement recorded.')
        ->with('filter_contributor', $contributor->Id);
}

    public function edit(MedicalFundDisbursement $disbursement)
    {
        $medical_fund = $disbursement->fund;
        $beneficiaries = $medical_fund->beneficiaries()->where('IsActive',true)->get();

        return view('bancassurance.medical_fund_disbursements.edit', compact('disbursement','medical_fund','beneficiaries'));
    }

    public function update(Request $request, MedicalFundDisbursement $disbursement)
    {
        $data = $request->validate([
            'BeneficiaryId'     => ['required','integer'],
            'DisbursementDate'  => ['required','date'],
            'Amount'            => ['required','numeric','min:0.01'],
            'Purpose'           => ['nullable','string','max:500'],
        ]);

        $disbursement->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $disbursement->FundId])
            ->with('success','Disbursement updated.');
    }

    public function destroy(MedicalFundDisbursement $disbursement)
    {
        $fundId = $disbursement->FundId;
        $disbursement->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $fundId])
            ->with('success','Disbursement deleted.');
    }

    public function remainingLimit(Request $request, MedicalFundContributor $contributor)
{
    $coverageId   = (int)$request->query('coverage_id');
    $beneficiaryId= $request->query('beneficiary_id') ? (int)$request->query('beneficiary_id') : null;
    $onDate       = $request->query('on_date') ? \Carbon\Carbon::parse($request->query('on_date')) : now();

    // load packages with coverages
    $contributor->load(['packages.coverages']);

    // Find coverage config from any active package
    $allowed = $contributor->packages->flatMap(fn($p)=> $p->coverages)->keyBy('Id');
    if (!$allowed->has($coverageId)) {
        return response()->json([
            'ok'=>false, 'message'=>'Coverage not available for this contributor.'
        ], 422);
    }

    $cov = $allowed->get($coverageId);
    $pivot = $cov->pivot; // AnnualLimit, PerVisitLimit, WaitingPeriodDays, Scope

    // find the specific contributor-package that contains this coverage
    $pkgWithCoverage = $contributor->packages->first(function($p) use ($coverageId){
        return $p->coverages->firstWhere('Id', $coverageId);
    });
    $subscribedOn = optional($pkgWithCoverage?->pivot)->SubscribedOn ?? $contributor->CreatedOn;

    // waiting period check
    $waitingOk = true;
    $waitingMsg = null;
    if ($pivot->WaitingPeriodDays) {
        $wpEnd = \Carbon\Carbon::parse($subscribedOn)->addDays($pivot->WaitingPeriodDays);
        if ($onDate->lt($wpEnd)) {
            $waitingOk = false;
            $waitingMsg = 'Waiting period not satisfied until '.$wpEnd->toDateString().'.';
        }
    }
    // Calculate used YTD
    $yearStart = $onDate->copy()->startOfYear();
    $yearEnd   = $onDate->copy()->endOfYear();

    $q = MedicalFundDisbursement::query()
        ->where('ContributorId', $contributor->ID)
        ->where('CoverageId', $coverageId)
        ->whereBetween('DisbursementDate', [$yearStart, $yearEnd]);

    if (($pivot->Scope ?? 'PerBeneficiary') === 'PerBeneficiary' && $beneficiaryId) {
        $q->where('BeneficiaryId', $beneficiaryId);
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

public function options(MedicalFund $medical_fund, MedicalFundContributor $contributor)
{
    // Ensure contributor belongs to this fund
    abort_unless((int)$contributor->FundID === (int)$medical_fund->ID, 404);

    // Beneficiaries
    $beneficiaries = $contributor->beneficiaries()
        ->where('IsActive', true)
        ->get();

    // Coverages via packages
    $contributor->load(['packages.coverages']);
    $coverages = $contributor->packages
        ->flatMap->coverages
        ->unique('Id')
        ->values()
        ->map(function($cov){
            return [
                'Id'   => $cov->Id,
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
