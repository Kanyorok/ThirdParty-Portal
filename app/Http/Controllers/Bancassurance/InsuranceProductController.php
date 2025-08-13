<?php


namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceProductController extends Controller
{
    // Show all products
    public function index()
    {
        $products = DB::table('t_InsuranceProducts')->orderByDesc('Id')->get();
        return view('bancassurance.products.index', compact('products'));
    }

    // Show create form
    public function create()
    {
        return view('bancassurance.products.create');
    }

    // Store product
    public function store(Request $request)
    {
        $request->validate([
            'Name' => 'required|string|max:100',
            'Type' => 'nullable|string|max:50',
            'Description' => 'nullable|string|max:255',
        ]);

        DB::table('t_InsuranceProducts')->insert([
            'Name' => $request->Name,
            'Type' => $request->Type,
            'Description' => $request->Description,
            'IsActive' => 1,
            'CreatedAt' => now()
        ]);

        return redirect()->route('bancassurance.products.index')->with('success', 'Product created successfully.');
    }

    // Edit product
    public function edit($id)
    {
        $product = DB::table('t_InsuranceProducts')->find($id);

        if (!$product) {
            return redirect()->route('bancassurance.products.index')->with('error', 'Product not found.');
        }

        return view('bancassurance.products.edit', compact('product'));
    }

    // Update product
    public function update(Request $request, $id)
    {
        $request->validate([
            'Name' => 'required|string|max:100',
            'Type' => 'nullable|string|max:50',
            'Description' => 'nullable|string|max:255',
        ]);

        DB::table('t_InsuranceProducts')->where('Id', $id)->update([
            'Name' => $request->Name,
            'Type' => $request->Type,
            'Description' => $request->Description,
        ]);

        return redirect()->route('bancassurance.products.index')->with('success', 'Product updated.');
    }

    public function mapForm($id)
{
    $product = DB::table('t_InsuranceProducts')->find($id);

    $providers = DB::table('t_InsuranceProviders')->pluck('Name', 'Id')->toArray();

    $policyTypes = DB::table('t_CodeDetails')
        ->where('CodeID', 'POLICY_TYPE')
        ->pluck('Description', 'Id')
        ->toArray();

    return view('bancassurance.products.map', compact('product', 'providers', 'policyTypes'));
}

public function storeMap(Request $request, $id)
{
    $request->validate([
        'InsuranceProviderID' => 'required|exists:t_InsuranceProviders,Id',
        'PolicyTypeID' => 'nullable|exists:t_CodeDetails,Id',
        'CustomName' => 'nullable|string|max:150',
        'CommissionType' => 'required|in:Flat,Tiered',
    ]);

    DB::table('t_InsuranceProviderProducts')->insert([
        'ProductID' => $id,
        'InsuranceProviderID' => $request->InsuranceProviderID,
        'PolicyTypeID' => $request->PolicyTypeID,
        'CustomName' => $request->CustomName,
        'CommissionType' => $request->CommissionType,
        'CreatedAt' => now(),
    ]);

    return redirect()->route('bancassurance.products.index')->with('success', 'Product mapped to provider successfully.');
}

}
