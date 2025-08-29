<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancassurancePolicy;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassuranceBeneficiaries;
use App\Services\Insurance\Customers\BancassuranceCustomersBeneficiariesService;
use App\Http\Requests\Insurance\Customers\BancassuranceCustomersBeneficiariesRequest;
use Illuminate\Support\Facades\Auth;

class CustomerBeneficiaryController extends Controller
{
    /**
     * Show form to create a beneficiary for a customer.
     */
    public function create()
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersBeneficiariesView, BancassuranceBeneficiaries::class);
        $customers = BancassuranceCustomer::all();
        $policies = BancassurancePolicy::all();
        $relationships = CodeDetail::where('CodeID', 'Relationships')->get();

        return view('bancassurance.customers.beneficiaries.create', compact('customers', 'relationships', 'policies'));
    }

    /**
     * Store a new beneficiary for the customer.
     */
    public function store(BancassuranceCustomersBeneficiariesRequest $request)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersBeneficiariesCreate, BancassuranceBeneficiaries::class);
        $validated = $request->validated();

        $CustomerID = BancassuranceCustomer::findOrFail($validated['CustomerID']);
        $PolicyID = BancassurancePolicy::findOrFail($validated['PolicyID']);
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
            Auth::user(),
        );

        return redirect()->route('bancassurance.customers.beneficiaries.create')->with('success', 'ADD BENEFICIARY saved.');
    }

}
