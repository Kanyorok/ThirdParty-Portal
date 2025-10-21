<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\MedicalFundContributorRequest;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundContributor;
use App\Models\ThirdParty\ThirdParties;
use Carbon\Carbon;

class MedicalFundContributorController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    /**
     * Display a listing of contributors under a given medical fund.
     */
    public function index(MedicalFund $medical_fund)
    {
        $q = $medical_fund->contributors()->with('thirdParty');

        if ($search = request('search')) {
            $q->where(function ($x) use ($search) {
                $x->where('ContributorNo', 'like', "%{$search}%")
                    ->orWhereHas('thirdParty', fn($q) =>
                        $q->where('Name', 'like', "%{$search}%")
                          ->orWhere('Email', 'like', "%{$search}%")
                          ->orWhere('Phone', 'like', "%{$search}%")
                    );
            });
        }

        if ($status = request('status')) {
            $q->where('Status', $status);
        }

        $contributors = $q->latest('Id')->paginate(20)->appends(request()->query());

        return view('bancassurance.medical_fund_contributors.index', compact('medical_fund', 'contributors'));
    }

    /**
     * Show the form for creating a new contributor.
     */
    public function create(MedicalFund $medical_fund)
    {
        $statuses = CodeDetail::query()
            ->where('CodeID', 'MedicalContributorStatus')
            ->orderBy('Description')
            ->get(['ID', 'Description']);

        $thirdParties = ThirdParties::query()
            ->orderBy('ThirdPartyName')
            ->get(['Id', 'ThirdPartyName']);

        return view('bancassurance.medical_fund_contributors.create', compact('medical_fund', 'statuses', 'thirdParties'));
    }

    /**
     * Store a newly created contributor in storage.
     */
    public function store(MedicalFundContributorRequest $request, MedicalFund $medical_fund)
    {
        $data = $request->validated();
        $data['FundId'] = $medical_fund->Id;

        // 🔢 Auto-generate unique Contributor Number
        $latest = MedicalFundContributor::where('FundId', $medical_fund->Id)
            ->orderByDesc('Id')
            ->first();

        $nextNo = $latest
            ? (intval(substr($latest->ContributorNo ?? 'CNT0000', 3)) + 1)
            : 1;

        $data['ContributorNo'] = 'CNT' . str_pad($nextNo, 4, '0', STR_PAD_LEFT);

        // Create contributor
        $contributor = MedicalFundContributor::create($data);

        // Sync selected packages
        $this->syncPackages($contributor, $request->input('package_ids', []));

        return redirect()
            ->route('bancassurance.contributors.show', $contributor->Id)
            ->with('success', 'Contributor created successfully with number ' . $data['ContributorNo']);
    }


    /**
     * Display the specified contributor details.
     */
    public function show(MedicalFundContributor $contributor)
    {
        $contributor->load(['fund', 'beneficiaries', 'packages']);

        $totals = [
            'contrib_sum' => $contributor->contributions()->sum('Amount'),
            'disb_sum'    => $contributor->disbursements()->sum('Amount'),
        ];

        // If you have a BeneficiaryRelationship model, replace this with its call.
        $relationships = collect([
            (object)['Code' => 'SELF', 'Name' => 'Self'],
            (object)['Code' => 'SPOUSE', 'Name' => 'Spouse'],
            (object)['Code' => 'CHILD', 'Name' => 'Child'],
            (object)['Code' => 'PARENT', 'Name' => 'Parent'],
            (object)['Code' => 'GUARDIAN', 'Name' => 'Guardian'],
            (object)['Code' => 'SIBLING', 'Name' => 'Sibling'],
            (object)['Code' => 'OTHER', 'Name' => 'Other'],
        ]);

        return view('bancassurance.medical_fund_contributors.show', compact('contributor', 'totals', 'relationships'));
    }

    /**
     * Show the form for editing the specified contributor.
     */
    public function edit($id)
    {
        $contributor  = MedicalFundContributor::with(['fund', 'packages'])->findOrFail($id);
        $medical_fund = $contributor->fund ?? MedicalFund::findOrFail($contributor->FundId);

        $statuses = CodeDetail::query()
            ->where('CodeID', 'MedicalContributorStatus')
            ->orderBy('Description')
            ->get(['ID', 'Description']);

        $thirdParties = ThirdParties::query()
            ->orderBy('ThirdPartyName')
            ->get(['Id', 'ThirdPartyName']);

        return view('bancassurance.medical_fund_contributors.edit', compact('contributor', 'medical_fund', 'statuses', 'thirdParties'));
    }

    /**
     * Update the specified contributor in storage.
     */
    public function update(MedicalFundContributorRequest $request, MedicalFundContributor $contributor)
    {
        $contributor->update($request->validated());
        $this->syncPackages($contributor, $request->input('package_ids', []));

        return redirect()
            ->route('bancassurance.contributors.show', $contributor->Id)
            ->with('success', 'Contributor updated successfully.');
    }

    /**
     * Soft delete the specified contributor.
     */
    public function destroy(MedicalFundContributor $contributor)
    {
        $fundId = $contributor->FundId;
        $contributor->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.contributors.index', ['medical_fund' => $fundId])
            ->with('success', 'Contributor archived successfully.');
    }

    /**
     * 🔁 Helper to sync packages with proper flags.
     */
    private function syncPackages(MedicalFundContributor $contributor, array $packageIds = [])
    {
        $ids = collect($packageIds)->map(fn($v) => (int)$v)->unique()->values();
        if ($ids->isEmpty()) {
            $contributor->packages()->detach();
            return;
        }

        $today = Carbon::now()->toDateString();

        $existing = $contributor->packages()
            ->whereIn('t_MedicalFundPackages.Id', $ids)
            ->get()
            ->mapWithKeys(fn($p) => [(int)$p->Id => optional($p->pivot)->SubscribedOn]);

        $primaryId = optional(
            $contributor->packages()->wherePivot('IsPrimary', 1)->first()
        )->Id ?? $ids->first();

        $sync = $ids->mapWithKeys(function ($pid) use ($existing, $today, $primaryId) {
            $subOn = $existing->get((int)$pid) ?: $today;
            return [
                $pid => [
                    'IsActive'     => 1,
                    'SubscribedOn' => $subOn,
                    'IsPrimary'    => $pid === $primaryId ? 1 : 0,
                ],
            ];
        })->all();

        $contributor->packages()->sync($sync);
    }
}
