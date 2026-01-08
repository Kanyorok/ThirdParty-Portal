<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundPackage;
use App\Http\Requests\Insurance\MedicalFundPackageRequest;

class MedicalFundPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(MedicalFund $medical_fund)
    {
        $packages = $medical_fund->packages()->orderBy('Name')->paginate(20);
        return view('bancassurance.medical_fund_packages.index', compact('medical_fund', 'packages'));
    }

    public function create(MedicalFund $medical_fund)
    {
        return view('bancassurance.medical_fund_packages.create', compact('medical_fund'));
    }

    public function store(MedicalFundPackageRequest $request, MedicalFund $medical_fund)
    {
        // 1) validated data from request
        $data = $request->validated();
        $data['FundId'] = $medical_fund->Id;

        // 2) create package
        $package = MedicalFundPackage::create($data);

        // 3) sync pivot coverage
        $this->syncCoverages($package, $request);

        return redirect()
            ->route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $medical_fund->Id])
            ->with('success', 'Package created successfully.');
    }

    public function edit(MedicalFundPackage $package)
    {
        $medical_fund = $package->fund;
        return view('bancassurance.medical_fund_packages.edit', compact('package', 'medical_fund'));
    }

    public function show(MedicalFundPackage $package)
    {
        $package->load(['fund', 'coverages']);
        return view('bancassurance.medical_fund_packages.show', compact('package'));
    }

    public function update(MedicalFundPackageRequest $request, MedicalFundPackage $package)
    {
        // 1) update main fields
        $package->update($request->validated());

        // 2) sync pivot coverage
        $this->syncCoverages($package, $request);

        return redirect()
            ->route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $package->FundId])
            ->with('success', 'Package updated successfully.');
    }

    public function destroy(MedicalFundPackage $package)
    {
        $fundId = $package->FundId;
        $package->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $fundId])
            ->with('success', 'Package deleted successfully.');
    }

    /**
     * Handle syncing of coverage pivot table.
     */
    private function syncCoverages(MedicalFundPackage $package, $request): void
    {
        $sync = [];
        $ids = collect($request->input('coverage_ids', []))
            ->map(fn($v) => (int) $v)
            ->filter()
            ->unique();

        foreach ($ids as $cid) {
            $sync[$cid] = [
                'AnnualLimit'       => data_get($request, "coverage.AnnualLimit.$cid"),
                'PerVisitLimit'     => data_get($request, "coverage.PerVisitLimit.$cid"),
                'WaitingPeriodDays' => data_get($request, "coverage.WaitingPeriod.$cid"),
                'Scope'             => data_get($request, "coverage.Scope.$cid") ?: 'PerBeneficiary',
                'IsActive'          => 1,
            ];
        }

        $package->coverages()->sync($sync);
    }
}
