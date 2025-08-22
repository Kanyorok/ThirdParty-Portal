<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PricingRuleController extends Controller
{
    public function index()
    {
        $rules = DB::table('t_InsurancePricingRules as r')
            ->join('t_InsuranceProviderProducts as ipp', 'r.ProviderProductID', '=', 'ipp.Id')
            ->join('t_InsuranceProviders as ip', 'ipp.InsuranceProviderID', '=', 'ip.Id')
            ->join('t_InsuranceProducts as p', 'ipp.ProductID', '=', 'p.Id')
            ->select(
                'r.*',
                'ip.Name as ProviderName',
                'p.Name as ProductName'
            )
            ->orderByDesc('r.Id')
            ->get();

        return view('bancassurance.pricing.index', compact('rules'));
    }

    public function create()
    {
        $providerProducts = DB::table('t_InsuranceProviderProducts as ipp')
            ->join('t_InsuranceProviders as ip', 'ipp.InsuranceProviderID', '=', 'ip.Id')
            ->join('t_InsuranceProducts as p', 'ipp.ProductID', '=', 'p.Id')
            ->select(
                'ipp.Id',
                'ip.Name as ProviderName',
                'p.Name as ProductName'
            )
            ->where('ipp.IsActive', 1)
            ->get();

        return view('bancassurance.pricing.create', compact('providerProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ProviderProductID' => 'required|exists:t_InsuranceProviderProducts,Id',
            'MinCoverage' => 'required|numeric|min:0',
            'MaxCoverage' => 'required|numeric|min:0|gte:MinCoverageAmount',
            'MinAge' => 'required|integer|min:0',
            'MaxAge' => 'required|integer|min:0|gte:MinAge',
            'MinTenureMonths' => 'required|integer|min:0',
            'MaxTenureMonths' => 'required|integer|min:0|gte:MinTenureMonths',
            'PremiumRate' => 'required|numeric|min:0',
            'Remarks' => 'nullable|string|max:255',
        ]);

        DB::table('t_InsurancePricingRules')->insert([
            'ProviderProductID' => $request->ProviderProductID,
            'MinCoverage' => $request->MinCoverageAmount,
            'MaxCoverage' => $request->MaxCoverageAmount,
            'MinAge' => $request->MinAge,
            'MaxAge' => $request->MaxAge,
            'MinTenureMonths' => $request->MinTenureMonths,
            'MaxTenureMonths' => $request->MaxTenureMonths,
            'PremiumRate' => $request->PremiumRate,
            'Remarks' => $request->Remarks,
            'CreatedAt' => now(),
        ]);

        return redirect()->route('bancassurance.pricing.index')->with('success', 'Pricing rule saved successfully.');
    }

    public function edit($id)
    {
        $rule = DB::table('t_InsurancePricingRules')->where('Id', $id)->first();
        $providerProducts = DB::table('t_InsuranceProviderProducts')->where('IsActive', 1)->get();

        return view('bancassurance.pricing.edit', compact('rule', 'providerProducts'));
    }

    public function update(Request $request, $id)
    {
        DB::table('t_InsurancePricingRules')->where('Id', $id)->update([
            'ProviderProductID' => $request->ProviderProductID,
            'MinAge' => $request->MinAge,
            'MaxAge' => $request->MaxAge,
            'MinCoverage' => $request->MinCoverage,
            'MaxCoverage' => $request->MaxCoverage,
            'MinTenure' => $request->MinTenure,
            'MaxTenure' => $request->MaxTenure,
            'PremiumRate' => $request->PremiumRate,
            'UpdatedAt' => now(),
        ]);

        return redirect()->route('bancassurance.pricing.index')->with('success', 'Pricing rule updated.');
    }

    public function destroy($id)
    {
        DB::table('t_InsurancePricingRules')->where('Id', $id)->delete();

        return redirect()->route('bancassurance.pricing.index')->with('success', 'Pricing rule deleted.');
    }
}

