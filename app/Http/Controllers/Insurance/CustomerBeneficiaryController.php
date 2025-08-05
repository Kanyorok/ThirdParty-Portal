<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceCustomers;
use App\Models\Insurance\BancassurancePolicies;
use App\Services\Insurance\Customers\BancassuranceCustomersBeneficiariesService;
use App\Http\Requests\Insurance\Customers\BancassuranceCustomersBeneficiariesRequest;

class CustomerBeneficiaryController extends Controller
{
    /**
     * Show form to create a beneficiary for a customer.
     */
    public function create()
    {
       $customers= BancassuranceCustomers::all();
       $policys= BancassurancePolicies::all();
       $relationships = CodeDetail::where('CodeID', 'Relationships')->get();
       
        return view('bancassurance.customers.beneficiaries.create', compact('customers','relationships','policys'));
    }

    /**
     * Store a new beneficiary for the customer.
     */
    public function store(BancassuranceCustomersBeneficiariesRequest $request)
    {
        $validated = $request->validated();

        $CustomerID = BancassuranceCustomers::findOrFail($validated['CustomerID']);
        $PolicyID = BancassurancePolicies::findOrFail($validated['PolicyID']);
        $Relationship = CodeDetail::findOrFail($validated['Relationship']);
        // $Relationship = CodeDetail::where('CodeID','Relationships')
        //     ->where('Description', $validated['Relationships'])
        //     ->firstOrFail();

        $customer = BancassuranceCustomersBeneficiariesService::create(
                $CustomerID,
                $PolicyID ?? '', 
                $validated['FullName'],
                $Relationship,
                $validated['IDNumber'],
                $validated['Phone'] ?? '',
                $validated['Email'],
                $validated['PercentageShare'],
                $validated['IsPrimary'],
                auth()->user()
            );

            return redirect()->route('bancassurance.customers.beneficiaries.create')->with('success', 'ADD BENEFICIARY saved.');
    }

}