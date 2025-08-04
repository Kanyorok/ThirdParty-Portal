<?php

namespace App\Http\Controllers\Insurance;

use App\Services\Insurance\BancassuranceCustomersService;
use App\Http\Requests\Insurance\Customers\BancassuranceCustomersRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Core\CodeDetail;
use App\Models\Insurance\BancAssuranceReferral;
use App\Models\Insurance\BancassuranceCustomers;


class CustomerController extends Controller
{
        //
    public function create()
    {
        $referrals = BancAssuranceReferral::all();
        $genders = CodeDetail::where('CodeID', 'Gender')->get();
        $maritalstatus = CodeDetail::where('CodeID', 'MaritalStatus')->get();
        $occupations = CodeDetail::where('CodeID', 'Occupation')->get();
        
        
        return view('bancassurance.customers.create',compact('genders','maritalstatus','occupations','referrals'));
    }

    public function store(BancassuranceCustomersRequest $request)
    {
        
        $validated = $request->validated();

        $ReferralID = BancAssuranceReferral::findOrFail($validated['ReferralID']);
        $Gender = CodeDetail::findOrFail($validated['Gender']);
        $MaritalStatus = CodeDetail::findOrFail($validated['MaritalStatus']);
        $Occupation = CodeDetail::findOrFail($validated['Occupation']);
        $DateOfBirth = new \DateTime($validated['DateOfBirth']);

        $customer = BancassuranceCustomersService::create(
                $ReferralID,
                $validated['FullName'],
                $validated['NationalID'],
                $validated['KRAPIN'],
                $DateOfBirth, 
                $Gender,  
                $MaritalStatus, 
                $validated['PhoneNumber'],
                $validated['Email'],
                $validated['Address'],
                $Occupation,
                auth()->user()
            );

        return redirect()->route('bancassurance.customers.index')->with('success', 'Customer profile saved.');
    }
    public function show($Id)
    {
        $customer = BancassuranceCustomers::findOrFail($Id); 

        return view('bancassurance.customers.show', compact('customer'));
    }
    public function index()
    {
       $customers = BancassuranceCustomers::all();

        return view('bancassurance.customers.index', compact('customers'));
    }

    public function check()
    {
       $customers = BancassuranceCustomers::all();

        return view('bancassurance.customers.check', compact('customers'));
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
public function edit($id)
    {
        // $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $customer = BancassuranceCustomers::findOrFail($id);  
        $referrals = BancAssuranceReferral::all();
        $genders = CodeDetail::where('CodeID', 'Gender')->get();
        $maritalstatus = CodeDetail::where('CodeID', 'MaritalStatus')->get();
        $occupations = CodeDetail::where('CodeID', 'Occupation')->get();
        

        return view('bancassurance.customers.edit', compact('customer', 'referrals', 'genders', 'occupations','maritalstatus' ));
    }

    public function update(BancassuranceCustomersRequest $request, $id)
    {
        // $this->authorize(PermissionEnum::PropertyTypeUpdate , PropertyType::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $customer = BancassuranceCustomers::findOrFail($id);

            $customer->update([
                'ReferralID' => $validated['ReferralID'],
                'FullName' => $validated['FullName'],
                'NationalID' => $validated['NationalID'],
                'KRAPIN' => $validated['KRAPIN'],
                'DateOfBirth' => $validated['DateOfBirth'],
                'Gender' => $validated['Gender'] ?? '',
                'PhoneNumber' => $validated['PhoneNumber'],
                'MaritalStatus' => $validated['MaritalStatus'], 
                'PhoneNumber' => $validated['PhoneNumber'],           
                'Email' => $validated['Email'],
                'Address' => $validated['Address'],
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
        //Check if user has permission to delete property categories
        //$this->authorize(PermissionEnum::PropertyTypeDelete , PropertyType::class);
        try {
            $customer = BancassuranceCustomers::findOrFail($id);
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


}


