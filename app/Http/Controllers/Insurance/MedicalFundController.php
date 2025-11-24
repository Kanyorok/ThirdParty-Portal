<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Insurance\MedicalFundRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\InsuranceProvider;
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

        $providers = InsuranceProvider::orderBy('Name')->get(['Id','Name']);

        $q = MedicalFund::query()->with('provider');

        // SEARCH (FundName and Provider Name)
        if ($s = $request->get('search')) {
            $q->where(function($x) use ($s){
                $x->where('FundName','like',"%{$s}%")
                  ->orWhereHas('provider', fn($p)=> $p->where('Name','like',"%{$s}%"));
            });
        }

        // FILTERS
        if ($pid = $request->get('provider_id')) {
            $q->where('ProviderId', $pid);
        }

        if (($active = $request->get('active')) !== null && $active !== '') {
            $q->where('IsActive', (int) $active);
        }

        if ($cov = $request->get('coverage_type')) {
            $q->where('CoverageType',$cov);
        }

        if ($min = $request->get('min_limit')) {
            $q->where('CoverageLimit','>=', (float)$min);
        }
        if ($max = $request->get('max_limit')) {
            $q->where('CoverageLimit','<=', (float)$max);
        }

        // Optional date filter (CreatedOn range)
        if ($from = $request->get('from')) {
            $q->whereDate('CreatedOn','>=',$from);
        }
        if ($to = $request->get('to')) {
            $q->whereDate('CreatedOn','<=',$to);
        }

        // Sort (default newest)
        $sort = $request->get('sort','created_desc');
        $sortMap = [
            'created_desc' => ['CreatedOn','desc'],
            'created_asc'  => ['CreatedOn','asc'],
            'name_asc'     => ['FundName','asc'],
            'name_desc'    => ['FundName','desc'],
            'limit_asc'    => ['CoverageLimit','asc'],
            'limit_desc'   => ['CoverageLimit','desc'],
        ];
        [$col,$dir] = $sortMap[$sort] ?? ['CreatedOn','desc'];
        $q->orderBy($col,$dir);

        $funds = $q->paginate(20)->appends($request->query());

        // Quick aggregates (respect filters)
        $totals = [
            'count'          => (clone $q)->count(),
            'active'         => (clone $q)->where('IsActive',1)->count(),
            'coverage_sum'   => (clone $q)->sum('CoverageLimit'),
            'avg_cov_limit'  => (clone $q)->avg('CoverageLimit'),
        ];

        $coverages = CodeDetail::where('CodeID', 'CoverType')->get()->keyBy('ID');

        return view('bancassurance.medical_funds.index', compact('funds','providers','totals','sort','coverages'));
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

    // Use camel-case variable name matching the blade (`$medical_fund`) to avoid undefined variable errors
    public function show(MedicalFund $medical_fund)
    {
        $this->authorize(PermissionEnum::MedicalFundView, MedicalFund::class);
        $medical_fund->load(['provider','beneficiaries','contributions','disbursements','packages.coverages','contributors']);
        // Pass the variable as `medical_fund` so the blade can access `$medical_fund`
        return view('bancassurance.medical_funds.show', compact('medical_fund'));
    }

    public function edit(MedicalFund $medical_fund)
    {
        $this->authorize(PermissionEnum::MedicalFundUpdate, MedicalFund::class);
        $providers = InsuranceProvider::query()->orderBy('Name')->get(['Id','Name']);
        $coverageTypes = CodeDetail::where('CodeID', 'CoverType')->get();
        return view('bancassurance.medical_funds.edit', compact('medical_fund','providers','coverageTypes'));
    }

    public function update(MedicalFundRequest $request, MedicalFund $medical_fund)
    {
        $this->authorize(PermissionEnum::MedicalFundUpdate, MedicalFund::class);
        $validated = $request->validated();

        // Refresh model from DB to ensure fresh data
        $medical_fund = MedicalFund::findOrFail($medical_fund->Id);
        $ProviderId = InsuranceProvider::findOrFail($validated['ProviderId']);
        $CoverageType = CodeDetail::findOrFail($validated['CoverageType']);

        $fundupdate = MedicalFundService::update(
            $medical_fund,
            $validated['FundName'],
            $ProviderId,
            $CoverageType,
            $validated['CoverageLimit'],
            $validated['Description'] ?? null,
            $validated['IsActive'] ?? false,
            Auth::user(),
        );

        return redirect()
            ->route('bancassurance.medicalfunds.index', $medical_fund->Id)
            ->with('success', 'Medical Fund updated.');
    }
    public function destroy(MedicalFund $medical_fund)
    {
        $medical_fund->delete();

        return redirect()
            ->route('bancassurance.medicalfunds.index')
            ->with('success','Medical Fund archived.');
    }
}
