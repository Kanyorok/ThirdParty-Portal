<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceProviderController extends Controller
{
    //
public function store(Request $request)
{
    $request->validate([
        'Name' => 'required|string|max:100',
        'Country' => 'nullable|string|max:50',
        'ContactPerson' => 'nullable|string|max:100',
        'Email' => 'nullable|email|max:100',
        'Phone' => 'nullable|string|max:50',
        'IsActive' => 'nullable|boolean',
    ]);

    DB::table('t_InsuranceProviders')->insert([
        'Name' => $request->Name,
        'Country' => $request->Country,
        'ContactPerson' => $request->ContactPerson,
        'Email' => $request->Email,
        'Phone' => $request->Phone,
        'IsActive' => $request->has('IsActive') ? 1 : 0,
        'CreatedAt' => now(),
    ]);

    return redirect()->route('bancassurance.insurers.index')->with('success', 'Insurance Provider registered.');
}

public function index()
{
    $providers = DB::table('t_InsuranceProviders')->orderByDesc('Id')->get();

    return view('bancassurance.insurers.index', compact('providers'));
}
public function edit($id)
{
    $provider = DB::table('t_InsuranceProviders')->where('Id', $id)->first();

    if (!$provider) {
        return redirect()->route('bancassurance.insurers.index')->with('error', 'Provider not found.');
    }

    return view('bancassurance.insurers.edit', compact('provider'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'Name' => 'required|string|max:100',
        'Country' => 'nullable|string|max:50',
        'ContactPerson' => 'nullable|string|max:100',
        'Email' => 'nullable|email|max:100',
        'Phone' => 'nullable|string|max:50',
        'IsActive' => 'required|boolean',
    ]);

    DB::table('t_InsuranceProviders')->where('Id', $id)->update([
        'Name' => $request->Name,
        'Country' => $request->Country,
        'ContactPerson' => $request->ContactPerson,
        'Email' => $request->Email,
        'Phone' => $request->Phone,
        'IsActive' => $request->IsActive,
    ]);

    return redirect()->route('bancassurance.insurers.index')->with('success', 'Provider updated successfully.');
}

public function viewProducts($id)
{
    $provider = DB::table('t_InsuranceProviders')->where('Id', $id)->first();

    $products = DB::table('t_InsuranceProviderProducts as ipp')
        ->join('t_InsuranceProducts as p', 'ipp.ProductID', '=', 'p.Id')
        ->leftJoin('t_CodeDetails as cd', 'ipp.PolicyTypeID', '=', 'cd.Id')
        ->where('ipp.InsuranceProviderID', $id)
        ->where('ipp.IsActive', 1)
        ->select('ipp.Id as MappingId', 'p.Name as ProductName', 'cd.Description as PolicyType', 'ipp.CommissionType')
        ->get();

    return view('bancassurance.insurers.products', compact('provider', 'products'));
}

public function detachProduct($providerId, $productId)
{
    DB::table('t_InsuranceProviderProducts')
        ->where('InsuranceProviderID', $providerId)
        ->where('ProductID', $productId)
        ->update(['IsActive' => 0]);

    return redirect()->route('bancassurance.insurers.products', $providerId)->with('success', 'Product detached successfully.');
}


}
