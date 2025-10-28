<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InsuranceProviderProductController extends Controller
{
    public function index()
    {
        $mappings = DB::table('t_InsuranceProviderProducts as ipp')
            ->join('t_InsuranceProviders as ip', 'ipp.ProviderID', '=', 'ip.Id')
            ->join('t_InsuranceProducts as p', 'ipp.ProductID', '=', 'p.Id')
            ->select('ipp.Id', 'ip.ProviderName', 'p.ProductName', 'ipp.CreatedOn')
            ->get();

        return view('insurance.providerproducts.index', compact('mappings'));
    }

    public function create()
    {
        $providers = DB::table('t_InsuranceProviders')->select('Id', 'ProviderName')->get();
        $products = DB::table('t_InsuranceProducts')->select('Id', 'ProductName')->get();

        return view('insurance.providerproducts.create', compact('providers', 'products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ProviderID' => 'required|integer',
            'ProductID' => 'required|integer',
        ]);

        try {
            // Check if mapping already exists
            $exists = DB::table('t_InsuranceProviderProducts')
                ->where('ProviderID', $validated['ProviderID'])
                ->where('ProductID', $validated['ProductID'])
                ->exists();

            if ($exists) {
                return redirect()->back()->with('error', 'This provider-product mapping already exists.');
            }

            DB::table('t_InsuranceProviderProducts')->insert([
                'ProviderID' => $validated['ProviderID'],
                'ProductID' => $validated['ProductID'],
                'CreatedBy' => Auth::id() ?? 1,
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id() ?? 1,
                'ModifiedOn' => now(),
            ]);

            return redirect()->route('insurance.providerproducts.index')->with('success', 'Provider-product mapping created successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to create provider-product mapping: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to create provider-product mapping.');
        }
    }

    public function detach($mappingId)
    {
        try {
            DB::table('t_InsuranceProviderProducts')
                ->where('Id', $mappingId)
                ->delete();

            return response()->json(['message' => 'Mapping detached successfully']);
        } catch (\Exception $e) {
            Log::error('Failed to detach provider-product mapping: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to detach mapping'], 500);
        }
    }
}
