<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancassurancePolicy;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassuranceBeneficiaries;
use App\Services\Insurance\Customers\BancassuranceCustomersBeneficiariesService;
use App\Http\Requests\Insurance\Customers\BancassuranceCustomersBeneficiariesRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class CustomerBeneficiaryController extends Controller
{
    /**
     * Show form to create a beneficiary for a customer.
     */
    public function create(Request $request)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersBeneficiariesView, BancassuranceBeneficiaries::class);
        $customers = BancassuranceCustomer::all();
        $policies = BancassurancePolicy::all();
        $relationships = CodeDetail::where('CodeID', 'Relationships')->get();

        // Prefill and lock customer if coming from list action
        $customerId = $request->query('customerId');
        $selectedCustomer = $customerId ? BancassuranceCustomer::find($customerId) : null;

        return view('bancassurance.customers.beneficiaries.create', compact('customers', 'relationships', 'policies', 'selectedCustomer'));
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

        return redirect()->route('bancassurance.customers.index')->with('success', 'ADD BENEFICIARY saved.');
    }

}
