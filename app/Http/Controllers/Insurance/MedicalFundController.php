<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Insurance\MedicalFund;
use App\Models\Insurance\InsuranceProvider;
use Illuminate\Http\Request;

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
        $providers = InsuranceProvider::orderBy('Name')->get(['ID','Name']);

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
            $q->where('ProviderID', $pid);
        }

        if (($active = $request->get('active')) !== null && $active !== '') {
            $q->where('IsActive', (int) $active);
        }

        if ($cov = $request->get('coverage_type')) {
            $q->where('CoverageType','like',"%{$cov}%");
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

        return view('bancassurance.medical_funds.index', compact('funds','providers','totals','sort'));
    }

    public function create()
    {
        $providers = InsuranceProvider::orderBy('Name')->get(['ID','Name']);
        return view('bancassurance.medical_funds.create', compact('providers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'FundName'       => ['required','string','max:255'],
            'ProviderID'     => ['required','integer'],
            'CoverageType'   => ['nullable','string','max:100'],
            'CoverageLimit'  => ['nullable','numeric','min:0'],
            'IsActive'       => ['nullable','boolean'],
            'Description'    => ['nullable','string','max:1000'],
        ]);
        $data['IsActive'] = (int)($request->boolean('IsActive'));

        $fund = MedicalFund::create($data);

        return match ($request->input('next')) {
            'packages' => redirect()
                ->route('bancassurance.medicalfunds.packages.index', ['medical_fund' => $fund->ID])
                ->with('success','Fund created. Now configure packages.'),
            'contributors' => redirect()
                ->route('bancassurance.medicalfunds.contributors.index', ['medical_fund' => $fund->ID])
                ->with('success','Fund created. Now add contributors.'),
            default => redirect()
                ->route('bancassurance.medicalfunds.show', ['medical_fund' => $fund->ID])
                ->with('success','Medical Fund created.'),
        };
    }

    public function show(MedicalFund $medical_fund)
    {
        // make sure we have provider, packages(+coverages), and contributors in one go
        $medical_fund->load([
            'provider',
            'packages.coverages',
            'contributors',
        ]);

        $totals = [
            'contrib_sum' => (float) $medical_fund->contributions()->sum('Amount'),
            'disb_sum'    => (float) $medical_fund->disbursements()->sum('Amount'),
        ];

        return view('bancassurance.medical_funds.show', compact('medical_fund','totals'));
    }

    public function edit(MedicalFund $medical_fund)
    {
        $providers = InsuranceProvider::orderBy('Name')->get(['ID','Name']);
        return view('bancassurance.medical_funds.edit', compact('medical_fund','providers'));
    }

    public function update(Request $request, MedicalFund $medical_fund)
    {
        $data = $request->validate([
            'FundName'      => ['required','string','max:255'],
            'ProviderID'    => ['required','integer'],
            'CoverageType'  => ['nullable','string','max:100'],
            'CoverageLimit' => ['nullable','numeric'],
            'Description'   => ['nullable','string'],
            'IsActive'      => ['nullable','boolean'],
        ]);
        $data['IsActive'] = (int)($request->boolean('IsActive'));

        $medical_fund->update($data);

        return redirect()
            ->route('bancassurance.medicalfunds.edit', ['medical_fund' => $medical_fund->ID])
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
