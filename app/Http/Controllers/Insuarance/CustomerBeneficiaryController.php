<?php

namespace App\Http\Controllers\Insuarance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerBeneficiaryController extends Controller
{
    /**
     * Show form to create a beneficiary for a customer.
     */
    public function create($customerId)
    {
        $customer = DB::table('t_BancassuranceCustomers')->where('Id', $customerId)->first();

        if (!$customer) {
            return redirect()->back()->with('error', 'Customer not found.');
        }

        return view('bancassurance.customers.beneficiaries.create', compact('customer'));
    }

    /**
     * Store a new beneficiary for the customer.
     */
    public function store(Request $request, $customerId)
    {
        $request->validate([
            'FullName' => 'required|string|max:255',
            'Relationship' => 'nullable|string|max:50',
            'IDNumber' => 'nullable|string|max:50',
            'Phone' => 'nullable|string|max:50',
            'Email' => 'nullable|email|max:100',
            'PercentageShare' => 'required|numeric|min:0|max:100',
            'IsPrimary' => 'nullable|boolean',
        ]);

        try {
            DB::table('t_BancassuranceBeneficiaries')->insert([
                'CustomerID' => $customerId,
                'FullName' => $request->FullName,
                'Relationship' => $request->Relationship ?? null,
                'IDNumber' => $request->IDNumber ?? null,
                'Phone' => $request->Phone ?? null,
                'Email' => $request->Email ?? null,
                'PercentageShare' => $request->PercentageShare,
                'IsPrimary' => $request->IsPrimary ? 1 : 0,
                'CreatedBy' => auth()->id(),
                'CreatedAt' => now(),
            ]);

            return redirect()->route('bancassurance.customers.portfolio', $customerId)
                ->with('success', 'Beneficiary added successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add beneficiary: ' . $e->getMessage());
        }
    }
}
