<?php

namespace App\Http\Controllers\Bancassurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    //
public function create()
{
    return view('bancassurance.customers.create');
}

public function store(Request $request)
{
    DB::table('t_BancassuranceCustomers')->insert([
        'FullName' => $request->FullName,
        'NationalID' => $request->NationalID,
        'KRA_PIN' => $request->KRA_PIN,
        'DateOfBirth' => $request->DateOfBirth,
        'Gender' => $request->Gender,
        'MaritalStatus' => $request->MaritalStatus,
        'Phone' => $request->Phone,
        'Email' => $request->Email,
        'Address' => $request->Address,
        'Occupation' => $request->Occupation,
        'CreatedBy' => auth()->id(),
        'CreatedAt' => now(),
    ]);

    return redirect()->route('bancassurance.customers.index')->with('success', 'Customer profile saved.');
}

public function portfolio($customerId)
{
    $customer = DB::table('t_BancassuranceCustomers')->where('Id', $customerId)->first();

    $policies = DB::table('t_BancassurancePolicies as p')
        ->join('t_InsuranceProducts as prod', 'p.ProductID', '=', 'prod.Id')
        ->join('t_InsuranceProviders as ins', 'p.InsurerID', '=', 'ins.Id')
        ->where('p.CustomerID', $customerId)
        ->select(
            'p.*',
            'prod.Name as ProductName',
            'ins.Name as InsurerName'
        )
        ->orderByDesc('p.PolicyStartDate')
        ->get();

    return view('bancassurance.customers.portfolio', compact('customer', 'policies'));
}

public function index(Request $request)
{
    $query = DB::table('t_BancassuranceCustomers');

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('FullName', 'like', "%{$search}%")
              ->orWhere('NationalID', 'like', "%{$search}%")
              ->orWhere('Phone', 'like', "%{$search}%");
        });
    }

    $customers = $query->orderBy('FullName')->get();

    return view('bancassurance.customers.index', compact('customers'));
}

}
