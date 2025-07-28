<?php

namespace App\Http\Controllers\Insurance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class InsuranceProductController extends Controller
{
    public function index()
    {
        $products = DB::table('t_InsuranceProducts as p')
            ->join('t_PolicyTypes as t', 'p.PolicyTypeID', '=', 't.Id')
            ->select('p.*', 't.PolicyTypeName')
            ->orderBy('ProductName')
            ->get();

        $types = DB::table('t_PolicyTypes')->where('Status', 'Active')->get();

        return view('insurance-products.index', compact('products', 'types'));
    }

    public function store(Request $request)
    {
        DB::table('t_InsuranceProducts')->insert([
            'ProductName' => $request->ProductName,
            'PolicyTypeID' => $request->PolicyTypeID,
            'CoverDescription' => $request->CoverDescription,
            'PremiumMode' => $request->PremiumMode,
            'Status' => $request->Status,
            'CreatedAt' => now(),
            'CreatedBy' => Auth::id()
        ]);

        return redirect()->route('insurance-products.index')->with('success', 'Product added.');
    }
}
