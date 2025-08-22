<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceProviderProductController extends Controller
{
public function Index()
{
    $mappedProducts = DB::table('t_InsuranceProviderProducts as ipp')
        ->join('t_InsuranceProviders as ip', 'ipp.InsuranceProviderID', '=', 'ip.Id')
        ->join('t_InsuranceProducts as p', 'ipp.ProductID', '=', 'p.Id')
        ->leftJoin('t_CodeDetails as cd', 'ipp.PolicyTypeID', '=', 'cd.Id')
        ->select(
            'ipp.*',
            'ip.Name as ProviderName',
            'p.Name as ProductName',
            'cd.Description as PolicyTypeName'
        )
        ->orderByDesc('ipp.Id')
        ->get();

    return view('bancassurance.products.mapped.index', compact('mappedProducts'));
}


    public function create()
    {
        $providers = DB::table('t_InsuranceProviders')->where('IsActive', 1)->pluck('Name', 'Id');
        $products = DB::table('t_InsuranceProducts')->where('IsActive', 1)->pluck('Name', 'Id');
        $policyTypes = DB::table('t_CodeDetails')->where('CodeID', 'POLICY_TYPE')->pluck('Description', 'Id');

        return view('bancassurance.products.mapped.create', compact('providers', 'products', 'policyTypes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'InsuranceProviderID' => 'required|exists:t_InsuranceProviders,Id',
            'ProductID' => 'required|exists:t_InsuranceProducts,Id',
            'PolicyTypeID' => 'required|exists:t_CodeDetails,Id',
            'CustomName' => 'nullable|string|max:100',
            'CommissionType' => 'required|in:Flat,Tiered',
        ]);

        DB::table('t_InsuranceProviderProducts')->insert([
            'InsuranceProviderID' => $request->InsuranceProviderID,
            'ProductID' => $request->ProductID,
            'PolicyTypeID' => $request->PolicyTypeID,
            'CustomName' => $request->CustomName,
            'CommissionType' => $request->CommissionType,
            'IsActive' => 1,
            'CreatedAt' => now(),
        ]);

        return redirect()->route('bancassurance.products.mapped.index')->with('success', 'Product mapped to provider.');
    }

public function detach($mappingId)
{
    DB::table('t_InsuranceProviderProducts')->where('Id', $mappingId)->delete();

    return redirect()->back()->with('success', 'Product detached successfully.');
}
}
