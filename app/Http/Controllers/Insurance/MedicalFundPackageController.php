<?php

namespace App\Http\Controllers\Insurance;


use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\MedicalFundPackage;
use Illuminate\Http\Request;

class MedicalFundPackageController extends Controller
{
    public function __construct(){ $this->middleware(['auth']); }

    // /bancassurance/medicalfunds/{medical_fund}/packages
    public function index(MedicalFund $medical_fund)
    {
        $packages = $medical_fund->packages()->orderBy('Name')->paginate(20);
        return view('bancassurance.medical_fund_packages.index', compact('medical_fund','packages'));
    }

    public function create(MedicalFund $medical_fund)
    {
        return view('bancassurance.medical_fund_packages.create', compact('medical_fund'));
    }

public function store(Request $request, MedicalFund $medical_fund)
{
    // 1) basic fields
    $data = $request->validate([
        'Name'                => ['required','string','max:100'],
        'CoverageDescription' => ['nullable','string','max:255'],
        'Premium'             => ['required','numeric','min:0'],
        'IsCompulsory'        => ['nullable','boolean'],
    ]);
    $data['FundID'] = $medical_fund->ID;

    // 2) create the package
    $package = MedicalFundPackage::create($data);

    // 3) coverage pivot (must be validated as arrays or Laravel will drop them)
    $request->validate([
        'coverage_ids'              => ['array'],
        'coverage_ids.*'            => ['integer'],
        'coverage.AnnualLimit.*'    => ['nullable','numeric','min:0'],
        'coverage.PerVisitLimit.*'  => ['nullable','numeric','min:0'],
        'coverage.WaitingPeriod.*'  => ['nullable','integer','min:0'],
        'coverage.Scope.*'          => ['nullable','in:PerBeneficiary,PerFamily'],
    ]);

    $sync = [];
    $ids = collect($request->input('coverage_ids', []))->map(fn($v)=>(int)$v)->filter()->unique();
    foreach ($ids as $cid) {
        $sync[$cid] = [
            'AnnualLimit'       => data_get($request, "coverage.AnnualLimit.$cid"),
            'PerVisitLimit'     => data_get($request, "coverage.PerVisitLimit.$cid"),
            'WaitingPeriodDays' => data_get($request, "coverage.WaitingPeriod.$cid"),
            'Scope'             => data_get($request, "coverage.Scope.$cid") ?: 'PerBeneficiary',
            'IsActive'          => 1,
        ];
    }
    $package->coverages()->sync($sync);   // ✅ writes to t_MedicalFundPackageCoverages

    // 4) redirect AFTER syncing
    return redirect()
        ->route('bancassurance.medicalfunds.packages.index', $medical_fund->ID)
        ->with('success','Package created.');
}

    public function edit(MedicalFundPackage $package)
    {
        $medical_fund = $package->fund;
        return view('bancassurance.medical_fund_packages.edit', compact('package','medical_fund'));
    }

    public function show(\App\Models\Insurance\MedicalFundPackage $package)
{
    $package->load(['fund','coverages']); // fund relation optional
    return view('bancassurance.medical_fund_packages.show', compact('package'));
}

public function update(Request $request, MedicalFundPackage $package)
{
    // 1) basic fields
    $data = $request->validate([
        'Name'                => ['required','string','max:100'],
        'CoverageDescription' => ['nullable','string','max:255'],
        'Premium'             => ['required','numeric','min:0'],
        'IsCompulsory'        => ['nullable','boolean'],
    ]);
    $package->update($data);

    // 2) coverage pivot
    $request->validate([
        'coverage_ids'              => ['array'],
        'coverage_ids.*'            => ['integer'],
        'coverage.AnnualLimit.*'    => ['nullable','numeric','min:0'],
        'coverage.PerVisitLimit.*'  => ['nullable','numeric','min:0'],
        'coverage.WaitingPeriod.*'  => ['nullable','integer','min:0'],
        'coverage.Scope.*'          => ['nullable','in:PerBeneficiary,PerFamily'],
    ]);

    $sync = [];
    $ids = collect($request->input('coverage_ids', []))->map(fn($v)=>(int)$v)->filter()->unique();
    foreach ($ids as $cid) {
        $sync[$cid] = [
            'AnnualLimit'       => data_get($request, "coverage.AnnualLimit.$cid"),
            'PerVisitLimit'     => data_get($request, "coverage.PerVisitLimit.$cid"),
            'WaitingPeriodDays' => data_get($request, "coverage.WaitingPeriod.$cid"),
            'Scope'             => data_get($request, "coverage.Scope.$cid") ?: 'PerBeneficiary',
            'IsActive'          => 1,
        ];
    }
    $package->coverages()->sync($sync);   // ✅ update pivot

    return redirect()
        ->route('bancassurance.medicalfunds.packages.index', $package->FundID)
        ->with('success','Package updated.');
}

    public function destroy(MedicalFundPackage $package)
    {
        $fundId = $package->FundID;
        $package->delete();
        return redirect()
            ->route('bancassurance.medicalfunds.packages.index', $fundId)
            ->with('success','Package archived.');
    }
}
