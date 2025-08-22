<?php

namespace App\Http\Controllers\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\HRM\Employee;
use App\Models\Core\CodeDetail;
use App\Enums\Core\PermissionEnum;
use App\Models\Insurance\BancassuranceCustomer;
use App\Models\Insurance\BancassuranceCustomerContact;
use App\Services\Insurance\Customers\BancassuranceCustomersContactsService;
use App\Http\Requests\Insurance\Customers\BancassuranceCustomersContactsRequest;

class CustomerCommunicationController extends Controller
{
    //
    public function create()
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersContactsView, BancassuranceCustomerContact::class);
        $customers = BancassuranceCustomer::all();
        $employees = Employee::all();
        $contacttypes = CodeDetail::where('CodeID', 'ContactType')->get();
        return view('bancassurance.customers.communication.create', compact('customers', 'employees', 'contacttypes'));
    }

    public function store(BancassuranceCustomersContactsRequest $request)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersContactsCreate, BancassuranceCustomerContact::class);
        $validated = $request->validated();
        $CustomerID = BancassuranceCustomer::findOrFail($validated['CustomerID']);
        $ContactDate = new \DateTime($validated['ContactDate']);
        $ContactType = CodeDetail::findOrFail($validated['ContactType']);
        $HandledBy = Employee::findOrFail($validated['HandledBy']);

        $customer = BancassuranceCustomersContactsService::create(
            $CustomerID,
            $ContactDate,
            $ContactType,
            $validated['Summary'],
            $validated['Notes'],
            $HandledBy,
            Auth::user(),
        );

        return redirect()->route('bancassurance.customers.communication.index')->with('success', 'Customer Contacts saved.');

    }

    public function index()
    {
        $customer = BancassuranceCustomer::all();
        $logs = BancassuranceCustomerContact::all();

        return view('bancassurance.customers.communication.index', compact('customer', 'logs'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersContactsView, BancassuranceCustomerContact::class);
        $log = BancassuranceCustomerContact::findOrFail($id);
        $customers = BancassuranceCustomer::all();
        $employees = Employee::all();
        $contacttypes = CodeDetail::where('CodeID', 'ContactType')->get();
        return view('bancassurance.customers.communication.edit', compact('log', 'customers', 'employees', 'contacttypes'));
    }

    public function update(BancassuranceCustomersContactsRequest $request, $id)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersContactsUpdate, BancassuranceCustomerContact::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $validated['ContactDate'] = \Carbon\Carbon::parse($validated['ContactDate'])->format('Y-m-d');
        } catch (\Exception $e) {
            return back()->withErrors(['ContactDate' => 'Invalid date format.'])->withInput();
        }

        try {
            $log = BancassuranceCustomerContact::findOrFail($id);

            $log->update([
                'CustomerID' => $validated['CustomerID'],
                'ContactDate' => $validated['ContactDate'],
                'ContactType' => $validated['ContactType'],
                'Summary' => $validated['Summary'],
                'Notes' => $validated['Notes'],
                'HandledBy' => $validated['HandledBy'] ?? '',
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($log)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated customer Contacts');

            return redirect()->route('bancassurance.customers.communication.index')->with('success', 'Customer Contacts updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update  customer contacts:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update customer contacts'])->withInput();
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::BancassuranceCustomersContactsDelete, BancassuranceCustomerContact::class);
        try {
            $log = BancassuranceCustomerContact::findOrFail($id);
            $log->delete();

            return redirect()->route('bancassurance.customers.communication.index')
                ->with('success', 'Customer Contacts Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Customer contacts: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Customer Contacts. Please try again.'])
                ->withInput();
        }
    }


}

