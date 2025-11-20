<?php

namespace App\Http\Controllers\Insurance;

use App\Enums\Core\PermissionEnum;
use App\Models\ThirdParty\ThirdParties;
use App\Services\Insurance\BancassuranceCustomersService;
use App\Http\Requests\Insurance\Customers\BancassuranceCustomersRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\BancassuranceCustomer;


class CustomerController extends Controller
{
    //
    public function index()
    {
        $customers = BancassuranceCustomer::all();

        return view('bancassurance.customers.index', compact('customers'));
    }
    public function create()
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersView, BancassuranceCustomer::class);
        $referrals = BancAssuranceReferral::all();
        $genders = CodeDetail::where('CodeID', 'Gender')->get();
        $maritalstatus = CodeDetail::where('CodeID', 'MaritalStatus')->get();
        $occupations = CodeDetail::where('CodeID', 'Occupation')->get();
        $ThirdPartyIds = ThirdParties::all();

        return view('bancassurance.customers.create', compact('genders', 'maritalstatus', 'occupations', 'referrals', 'ThirdPartyIds'));
    }

    public function store(BancassuranceCustomersRequest $request)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersCreate, BancassuranceCustomer::class);
        $validated = $request->validated();

        $ThirdPartyId = ThirdParties::findOrFail($validated['ThirdPartyId']);
        $ReferralID = BancAssuranceReferral::findOrFail($validated['ReferralID']);
        $Gender = CodeDetail::findOrFail($validated['Gender']);
        $MaritalStatus = CodeDetail::findOrFail($validated['MaritalStatus']);
        $Occupation = CodeDetail::findOrFail($validated['Occupation']);
        $DateOfBirth = new \DateTime($validated['DateOfBirth']);

        $customer = BancassuranceCustomersService::create(
            $ThirdPartyId,
            $ReferralID,
            $DateOfBirth,
            $Gender,
            $MaritalStatus,
            $Occupation,
            Auth::user(),
        );

        return redirect()->route('bancassurance.customers.index')->with('success', 'Customer profile saved.');
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersView, BancassuranceCustomer::class);

        $customer = BancassuranceCustomer::with('referrals.referredByEmployee', 'genders')->findOrFail($Id);

        return view('bancassurance.customers.show', compact('customer'));
    }

    public function check()
    {
        //check
        $customers = BancassuranceCustomer::all();

        return view('bancassurance.customers.check', compact('customers'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersView, BancassuranceCustomer::class);
        $customer = BancassuranceCustomer::findOrFail($id);
        $referrals = BancAssuranceReferral::all();
        $genders = CodeDetail::where('CodeID', 'Gender')->get();
        $maritalstatus = CodeDetail::where('CodeID', 'MaritalStatus')->get();
        $occupations = CodeDetail::where('CodeID', 'Occupation')->get();


        return view('bancassurance.customers.edit', compact('customer', 'referrals', 'genders', 'occupations', 'maritalstatus'));
    }

    public function update(BancassuranceCustomersRequest $request, $id)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersUpdate, BancassuranceCustomer::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $ThirdPartyId = ThirdParties::findOrFail($validated['ThirdPartyId']);
            $customer = BancassuranceCustomer::findOrFail($id);

            $customer->update([
                $ThirdPartyId,
                'ReferralID' => $validated['ReferralID'],
                'DateOfBirth' => $validated['DateOfBirth'],
                'Gender' => $validated['Gender'] ?? '',
                'MaritalStatus' => $validated['MaritalStatus'],
                'Occupation' => $validated['Occupation'],
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($customer)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated customer');

            return redirect()->route('bancassurance.customers.check')->with('success', 'Customer updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update type:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update type'])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersDelete, BancassuranceCustomer::class);
        try {
            $customer = BancassuranceCustomer::findOrFail($id);

                if ($customer->policies()->exists()) {
                    return redirect()->back()
                    ->withErrors(['error' => 'This customer is in use and cannot be deleted.']);
                }  
            $customer->delete();

            return redirect()->route('bancassurance.customers.check')
                ->with('success', 'Customer Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Customer: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Customer. Please try again.'])
                ->withInput();
        }
    }

    public function portfolio($customerId)
    {
        $customer = BancassuranceCustomer::find($customerId);
        if (!$customer) {
            return redirect()->back()->withErrors(['error' => 'Customer not found.']);
        }

        $policies = $customer->policies()
            ->with(['product'])
            ->orderByDesc('PolicyStartDate')
            ->get();

        return view('bancassurance.customers.portfolio', compact('customer', 'policies'));
    }

}


