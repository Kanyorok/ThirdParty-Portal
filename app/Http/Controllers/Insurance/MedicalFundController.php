<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\MedicalFundRequest;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\InsuranceProvider; // adjust if needed
use App\Services\Insurance\MedicalFundService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MedicalFundController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // $this->middleware('permission:bancassurance.medicalfunds.view')->only('index','show');
        // $this->middleware('permission:bancassurance.medicalfunds.create')->only('create','store');
        // $this->middleware('permission:bancassurance.medicalfunds.edit')->only('edit','update');
        // $this->middleware('permission:bancassurance.medicalfunds.delete')->only('destroy');
    }

    public function index(Request $request)
    {
        $q = MedicalFund::query()
            ->with('provider')
            ->when($request->get('search'), function ($qr, $s) {
                $qr->where('FundName','like',"%{$s}%");
            })
            ->orderBy('ID','desc');

        $funds = $q->paginate(20)->appends($request->query());

        return view('bancassurance.medical_funds.index', compact('funds'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::MedicalFundCreate, MedicalFund::class);
        $providers = InsuranceProvider::query()->orderBy('Name')->get(['Id','Name']);
        $coverageTypes = CodeDetail::where('CodeID', 'CoverType')->get();
        return view('bancassurance.medical_funds.create', compact('providers','coverageTypes'));
    }

    public function store(MedicalFundRequest $request)
    {
        $this->authorize(PermissionEnum::MedicalFundCreate, MedicalFund::class);
        $validated = $request->validated();

        $ProviderId = InsuranceProvider::findOrFail($validated['ProviderId']);
        $CoverageType = CodeDetail::findOrFail($validated['CoverageType']);

        $fund = MedicalFundService::create(
            $validated['FundName'],
            $ProviderId,
            $CoverageType,
            $validated['CoverageLimit'],
            $validated['Description'] ?? null,
            $validated['IsActive'] ?? false,
            Auth::user(),
        );

        return redirect()
            ->route('bancassurance.medicalfunds.index')
            ->with('success', 'Medical Fund created.');
    }

    public function show(MedicalFund $medicalfund)
    {
        $this->authorize(PermissionEnum::MedicalFundView, MedicalFund::class);
        $medicalfund->load(['provider','beneficiaries','contributions','disbursements']);
        return view('bancassurance.medical_funds.show', compact('medicalfund'));
    }

    public function edit(MedicalFund $medicalfund)
    {
        $this->authorize(PermissionEnum::MedicalFundUpdate, MedicalFund::class);
        $providers = InsuranceProvider::query()->orderBy('Name')->get(['Id','Name']);
        $coverageTypes = CodeDetail::where('CodeID', 'CoverType')->get();
        return view('bancassurance.medical_funds.edit', compact('medicalfund','providers','coverageTypes'));
    }

    public function update(MedicalFundRequest $request, MedicalFund $medicalfund)
    {
        $this->authorize(PermissionEnum::MedicalFundUpdate, MedicalFund::class);
        $validated = $request->validated();

        $medicalfund = MedicalFund::findOrfail($medicalfund->Id);
        $ProviderId = InsuranceProvider::findOrFail($validated['ProviderId']);
        $CoverageType = CodeDetail::findOrFail($validated['CoverageType']);

        $fundupdate = MedicalFundService::update(
            $medicalfund,
            $validated['FundName'],
            $ProviderId,
            $CoverageType,
            $validated['CoverageLimit'],
            $validated['Description'] ?? null,
            $validated['IsActive'] ?? false,
            Auth::user(),
        );

        return redirect()
            ->route('bancassurance.medicalfunds.index', $medicalfund->Id)
            ->with('success', 'Medical Fund updated.');
    }

    public function destroy(MedicalFund $medicalfund)
    {
        $this->authorize(PermissionEnum::MedicalFundDelete, MedicalFund::class);
        $medicalfund->delete();
        return redirect()->route('bancassurance.medicalfunds.index')
            ->with('success','Medical Fund archived.');
    }
}
