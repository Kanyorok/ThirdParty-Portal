<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\MedicalFundDisbursementRequest;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContributor;
use App\Models\Insurance\MedicalFundDisbursement;
use App\Models\Insurance\MedicalFundBeneficiary;
use App\Services\Insurance\MedicalFundDisbursementService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MedicalFundDisbursementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display disbursements for a given medical fund.
     */
    public function index(MedicalFund $medical_fund, Request $request)
    {
        $q = MedicalFundDisbursement::where('FundId', $medical_fund->Id);

        $contributor = null;
        if ($cid = (int)$request->query('contributor')) {
            $q->where('ContributorId', $cid);
            $contributor = MedicalFundContributor::where('FundId', $medical_fund->Id)->find($cid);
        }

        $total = (float)$q->clone()->sum('Amount');
        $disbursements = $q->orderByDesc('DisbursementDate')
            ->paginate(20)
            ->appends($request->query());

        return view('bancassurance.medical_fund_disbursements.index', compact(
            'medical_fund',
            'disbursements',
            'total',
            'contributor'
        ));
    }

    /**
     * Show form for creating a new disbursement.
     */
    public function create(MedicalFund $medical_fund, Request $request)
    {
        $contributor   = null;
        $contributors  = collect();
        $beneficiaries = collect();
        $coverages     = collect();

        if ($cid = (int)$request->query('contributor')) {
            $contributor = MedicalFundContributor::where('FundId', $medical_fund->Id)->find($cid);

            if ($contributor) {
                $beneficiaries = $contributor->beneficiaries()->where('IsActive', true)->get()
                    ->map(fn($b) => [
                        'ID' => $b->Id,
                        'FullName' => $b->FullName,
                    ]);

                // Try the straightforward Eloquent path first (packages->coverages)
                $rawCoverages = $contributor->packages()
                    ->with('coverages')
                    ->get()
                    ->flatMap->coverages;

                // Normalize to array-like objects with a canonical Id property then dedupe
                $coverages = $rawCoverages->map(function($c){
                    return (object)[
                        'Id' => $c->ID ?? $c->Id ?? null,
                        'Name' => $c->Name ?? ($c->name ?? null),
                        'pivot' => $c->pivot ?? null,
                        '_orig' => $c,
                    ];
                })->filter(fn($c) => !is_null($c->Id))
                  ->unique('Id')
                  ->values();

                // Fallback: if relationships returned empty (possible pivot/column-name mismatches),
                // query the pivot table directly and join to t_Coverages to get canonical rows.
                if ($coverages->isEmpty()) {
                    $packageIds = $contributor->packages()->pluck('Id')->filter()->values();
                    if ($packageIds->isEmpty()) {
                        $packageIds = $contributor->packages()->pluck('ID')->filter()->values();
                    }

                    if ($packageIds->isNotEmpty()) {
                        $pivot = 't_MedicalFundPackageCoverages';
                        $pkgCols = ['PackageId', 'PackageID'];
                        $covCols = ['CoverageId', 'CoverageID'];

                        foreach ($pkgCols as $pkgCol) {
                            foreach ($covCols as $covCol) {
                                try {
                                    $rows = \DB::table($pivot . ' as pc')
                                        ->join('t_Coverages as c', 'pc.' . $covCol, '=', 'c.ID')
                                        ->whereIn('pc.' . $pkgCol, $packageIds->all())
                                        ->select('c.ID as ID', 'c.Name', 'pc.AnnualLimit', 'pc.PerVisitLimit', 'pc.WaitingPeriodDays', 'pc.Scope')
                                        ->get();

                                    if ($rows->isNotEmpty()) {
                                        $coverages = $rows->map(fn($r) => (object)[
                                            'Id' => $r->ID,
                                            'Name' => $r->Name,
                                            'pivot' => (object)[
                                                'AnnualLimit' => $r->AnnualLimit ?? null,
                                                'PerVisitLimit' => $r->PerVisitLimit ?? null,
                                                'WaitingPeriodDays' => $r->WaitingPeriodDays ?? null,
                                                'Scope' => $r->Scope ?? null,
                                            ],
                                            '_orig' => $r,
                                        ])->values();

                                        break 2;
                                    }
                                } catch (\Throwable $e) {
                                    // ignore and try next variant
                                }
                            }
                        }
                    }
                }
            }
        } else {
            $contributors = MedicalFundContributor::where('FundId', $medical_fund->Id)->get();
        }

        return view('bancassurance.medical_fund_disbursements.create', compact(
            'medical_fund',
            'contributor',
            'contributors',
            'beneficiaries',
            'coverages'
        ));
    }

    /**
     * Store a new medical fund disbursement using the service.
     */
    public function store(MedicalFundDisbursementRequest $request, MedicalFund $medical_fund)
    {
        $data = $request->validated();

        // Ensure contributor belongs to this fund
        $contributor = MedicalFundContributor::where('FundId', $medical_fund->Id)
            ->findOrFail($data['ContributorId']);

        // Ensure beneficiary belongs to contributor
        $beneficiary = MedicalFundBeneficiary::where('ContributorId', $contributor->Id)
            ->findOrFail($data['BeneficiaryId']);

        // Find package that includes the chosen coverage
        $contributor->load(['packages.coverages']);
        $package = null;
        foreach ($contributor->packages as $pck) {
            if ($pck->coverages->firstWhere('Id', (int)$data['CoverageId'])) { $package = $pck; break; }
        }

        // Create disbursement using service
        MedicalFundDisbursementService::create(
            $medical_fund,
            $contributor,
            $beneficiary,
            $data['CoverageId'],
            $package?->Id,
            Carbon::parse($data['DisbursementDate']),
            (float)$data['Amount'],
            $data['Purpose'] ?? null,
            Auth::user()
        );

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $medical_fund->Id])
            ->with('success', 'Disbursement recorded.')
            ->with('filter_contributor', $contributor->Id);
    }

    /**
     * Edit disbursement form.
     */
    public function edit(MedicalFundDisbursement $disbursement)
    {
        $medical_fund = $disbursement->fund;
        $beneficiaries = $medical_fund->beneficiaries()->where('IsActive', true)->get();

        return view('bancassurance.medical_fund_disbursements.edit', compact(
            'disbursement',
            'medical_fund',
            'beneficiaries'
        ));
    }

    /**
     * Update an existing disbursement.
     */
    public function update(MedicalFundDisbursementRequest $request, MedicalFundDisbursement $disbursement)
    {
        $data = $request->validated();

        $service = new MedicalFundDisbursementService($disbursement);
        $service->update(
            Carbon::parse($data['DisbursementDate']),
            (float)$data['Amount'],
            $data['Purpose'] ?? null,
            Auth::user()
        );

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $disbursement->FundId])
            ->with('success', 'Disbursement updated.');
    }

    /**
     * Delete a disbursement.
     */
    public function destroy(MedicalFundDisbursement $disbursement)
    {
        $fundId = $disbursement->FundId;

        $service = new MedicalFundDisbursementService($disbursement);
        $service->delete(Auth::user());

        return redirect()
            ->route('bancassurance.medicalfunds.disbursements.index', ['medical_fund' => $fundId])
            ->with('success', 'Disbursement deleted.');
    }

    /**
     * AJAX: Calculate remaining annual limit for a given coverage & contributor.
     */
    public function remainingLimit(Request $request, MedicalFundContributor $contributor)
    {
        // unchanged for now (can later move into service helper if needed)
        $coverageId    = (int)$request->query('coverage_id');
        $beneficiaryId = $request->query('beneficiary_id') ? (int)$request->query('beneficiary_id') : null;
        $onDate        = $request->query('on_date') ? Carbon::parse($request->query('on_date')) : now();

        $contributor->load(['packages.coverages']);
        $allowed = $contributor->packages->flatMap(fn($p) => $p->coverages)->keyBy('Id');

        if (!$allowed->has($coverageId)) {
            return response()->json(['ok' => false, 'message' => 'Coverage not available for this contributor.'], 422);
        }

        $cov = $allowed->get($coverageId);
        $pivot = $cov->pivot;

        $pkgWithCoverage = null;
        foreach ($contributor->packages as $pck) {
            if ($pck->coverages->firstWhere('Id', $coverageId)) { $pkgWithCoverage = $pck; break; }
        }
        $subscribedOn = optional($pkgWithCoverage?->pivot)->SubscribedOn ?? $contributor->CreatedOn;

        $waitingOk = true;
        $waitingMsg = null;
        if ($pivot->WaitingPeriodDays) {
            $wpEnd = Carbon::parse($subscribedOn)->addDays($pivot->WaitingPeriodDays);
            if ($onDate->lt($wpEnd)) {
                $waitingOk = false;
                $waitingMsg = 'Waiting period not satisfied until ' . $wpEnd->toDateString() . '.';
            }
        }

        $yearStart = $onDate->copy()->startOfYear();
        $yearEnd = $onDate->copy()->endOfYear();

        $q = MedicalFundDisbursement::query()
            ->where('ContributorId', $contributor->Id)
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

    /**
     * AJAX: Return options (beneficiaries + coverages) for contributor.
     */
    public function options(MedicalFund $medical_fund, MedicalFundContributor $contributor)
    {
    // ensure contributor belongs to this fund (attribute names are FundId / Id)
    abort_unless((int)$contributor->FundId === (int)$medical_fund->Id, 404);

        $beneficiaries = $contributor->beneficiaries()->where('IsActive', true)->get()
            ->map(fn($b) => [
                'ID' => $b->Id,
                'FullName' => $b->FullName,
            ]);

        // Attempt to get coverages via Eloquent relations first
        $contributor->load(['packages.coverages']);
        $coverages = collect();

        $raw = $contributor->packages->flatMap(function($p){ return $p->coverages ?? collect(); });
        if ($raw->isNotEmpty()) {
            $coverages = $raw->unique(function($c){ return $c->ID ?? $c->Id; })->values()->map(function($cov){
                return [
                    'ID' => $cov->ID ?? $cov->Id,
                    'Name' => $cov->Name ?? ($cov->name ?? ''),
                    'AnnualLimit' => $cov->pivot->AnnualLimit ?? $cov->AnnualLimit ?? null,
                    'PerVisitLimit' => $cov->pivot->PerVisitLimit ?? $cov->PerVisitLimit ?? null,
                    'WaitingPeriodDays' => $cov->pivot->WaitingPeriodDays ?? $cov->WaitingPeriodDays ?? null,
                    'Scope' => $cov->pivot->Scope ?? $cov->Scope ?? 'PerBeneficiary',
                ];
            });
        }

        // Fallback: if no coverages found via relations, query pivot tables directly.
        if ($coverages->isEmpty()) {
            // First try to get package IDs from contributor-package pivot table (two common column name conventions)
            $pkgPivot = 't_MedicalFundContributorPackages';
            $pkgCols = ['ContributorId','ContributorID'];
            $pkgIdCols = ['PackageId','PackageID'];
            $packageIds = collect();
            foreach ($pkgCols as $pc) {
                foreach ($pkgIdCols as $pIdCol) {
                    try {
                        $found = \DB::table($pkgPivot)->where($pc, $contributor->Id)->pluck($pIdCol)->filter()->unique();
                        if ($found->isNotEmpty()) { $packageIds = $found; break 2; }
                    } catch (\Throwable $e) {
                        // try next
                    }
                }
            }

            if ($packageIds->isNotEmpty()) {
                $pivot = 't_MedicalFundPackageCoverages';
                $pkgCols = ['PackageId','PackageID'];
                $covCols = ['CoverageId','CoverageID'];

                foreach ($pkgCols as $pkgCol) {
                    foreach ($covCols as $covCol) {
                        try {
                            $rows = \DB::table($pivot . ' as pc')
                                ->join('t_Coverages as c', 'pc.' . $covCol, '=', 'c.ID')
                                ->whereIn('pc.' . $pkgCol, $packageIds->all())
                                ->select('c.ID as ID', 'c.Name', 'pc.AnnualLimit', 'pc.PerVisitLimit', 'pc.WaitingPeriodDays', 'pc.Scope')
                                ->get();

                            if ($rows->isNotEmpty()) {
                                $coverages = $rows->map(function($r){
                                    return [
                                        'ID' => $r->ID,
                                        'Name' => $r->Name,
                                        'AnnualLimit' => $r->AnnualLimit ?? null,
                                        'PerVisitLimit' => $r->PerVisitLimit ?? null,
                                        'WaitingPeriodDays' => $r->WaitingPeriodDays ?? null,
                                        'Scope' => $r->Scope ?? 'PerBeneficiary',
                                    ];
                                })->values();

                                break 2;
                            }
                        } catch (\Throwable $e) {
                            // ignore and continue
                        }
                    }
                }
            }
        }

        return response()->json([
            'ok' => true,
            'beneficiaries' => $beneficiaries,
            'coverages' => $coverages,
        ]);
    }
}
