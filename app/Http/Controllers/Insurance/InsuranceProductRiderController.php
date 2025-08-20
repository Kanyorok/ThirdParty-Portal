<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceProductRiderController extends Controller
{
    public function index()
    {
        $riders = DB::table('t_InsuranceProductRiders as r')
            ->leftJoin('t_InsuranceProviderProducts as pp', 'r.ProviderProductID', '=', 'pp.Id')
            ->leftJoin('t_InsuranceProducts as p', 'pp.ProductID', '=', 'p.Id')
            ->leftJoin('t_InsuranceProviders as ip', 'pp.InsuranceProviderID', '=', 'ip.Id')
            ->select('r.*', 'p.Name as ProductName', 'ip.Name as ProviderName')
            ->orderByDesc('r.Id')
            ->get();

        return view('bancassurance.riders.index', compact('riders'));
    }

    public function create()
    {
        $mappedProducts = DB::table('t_InsuranceProviderProducts as pp')
            ->leftJoin('t_InsuranceProducts as p', 'pp.ProductID', '=', 'p.Id')
            ->leftJoin('t_InsuranceProviders as ip', 'pp.InsuranceProviderID', '=', 'ip.Id')
            ->select(
                'pp.Id',
                DB::raw("CONCAT(ip.Name, ' - ', p.Name) AS MappedProduct")
            )
            ->where('pp.IsActive', 1)
            ->get();

        return view('bancassurance.riders.create', compact('mappedProducts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'ProviderProductID' => 'required|exists:t_InsuranceProviderProducts,Id',
            'RiderName' => 'required|string|max:100',
            'Description' => 'nullable|string|max:255',
            'AdditionalPremium' => 'nullable|numeric|min:0',
            'IsOptional' => 'required|boolean'
        ]);

        DB::table('t_InsuranceProductRiders')->insert([
            'ProviderProductID' => $request->ProviderProductID,
            'RiderName' => $request->RiderName,
            'Description' => $request->Description,
            'AdditionalPremium' => $request->AdditionalPremium ?? 0.00,
            'IsOptional' => $request->IsOptional,
            'IsActive' => 1,
            'CreatedAt' => now(),
        ]);

        return redirect()->route('bancassurance.riders.index')->with('success', 'Rider added successfully.');
    }
}
