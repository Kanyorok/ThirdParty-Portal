<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    //
public function create()
{
    $providers = DB::table('t_InsuranceProviders')->where('IsActive', 1)->pluck('Name', 'Id')->toArray();
    $policyTypes = DB::table('t_CodeDetails')->where('CodeID', 'POLICY_TYPE')->pluck('Description', 'Id')->toArray();

    return view('bancassurance.products.create', compact('providers', 'policyTypes'));
}

public function store(Request $request)
{
    $request->validate([
        'InsuranceProviderID' => 'required|exists:t_InsuranceProviders,Id',
        'ProductName' => 'required|string|max:150',
        'PolicyTypeID' => 'required|exists:t_CodeDetails,Id',
        'Description' => 'nullable|string|max:500'
    ]);

    DB::table('t_BancassuranceProducts')->insert([
        'InsuranceProviderID' => $request->InsuranceProviderID,
        'ProductName' => $request->ProductName,
        'PolicyTypeID' => $request->PolicyTypeID,
        'Description' => $request->Description,
        'CreatedAt' => now()
    ]);

    return redirect()->route('bancassurance.products.index')->with('success', 'Product saved.');
}

}
