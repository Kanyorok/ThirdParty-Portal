<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyLeaseRenewalRequest;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Property\TenantAndLease\PropertyLeaseRenewalService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PropertyLeaseRenewalController extends Controller

{
    //
    public function index()
    {
        $leaserenewals = PropertyLeaseRenewal::with(['tenant', 'property'])->get();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.index', compact('leaserenewals'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyLeaseRenewalCreate, PropertyLeaseRenewal::class);
        $newleases = PropertyNewLease::with('getPropertyByTenant', 'getLeaseByProperty')->get();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.create', compact('newleases'));
    }

    public function getPropertyByTenant($tenantId)
    {
        $newlease = PropertyNewLease::where('TenantId', $tenantId)->get();
        return response()->json($newlease);
    }

    public function getLeaseByProperty($propertyId)
    {
        $newlease = PropertyNewLease::where('PropertyId', $propertyId)->get();
        return response()->json($newlease);
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseRenewalView, PropertyLeaseRenewal::class);
        $leaserenewal = PropertyLeaseRenewal::findOrFail($id);
        return view('property.tenantmanagement.leasemanagement.leaserenewal.show', compact('leaserenewal'));
    }

    public function store(PropertyLeaseRenewalRequest $request)
    {
        try {
            $validated = $request->validated();
            $leaseId = (int)$validated['LeaseId'];
            $tenantId = (int)$validated['TenantId'];
            $propertyId = (int)$validated['PropertyId'];
            $paymentFrequencyId = (int)$validated['PaymentFrequency'];

            // Use the lease ID to get the full lease
            $lease = PropertyNewLease::findOrFail($leaseId);
            PropertyLeaseRenewalService::create(
                $leaseId,                  // from DB
                $tenantId,
                $propertyId,
                $paymentFrequencyId,
                $validated['EndDateCurrentLease'],
                $validated['NewStartDate'],
                $validated['NewEndDate'],
                $validated['NewMonthlyRent'],
                $validated['Remarks'],
                Auth::user()
            );

            return redirect()->route('renewlease.index')->with('success', 'Lease renewal created successfully');
        } catch (Exception $e) {
            // Redirect back with error message
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function edit($Id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::PropertyLeaseRenewalUpdate, PropertyLeaseRenewal::class);
        $leaserenewal = PropertyLeaseRenewal::findOrFail($Id);
        $newleases = PropertyNewLease::with('getPropertyByTenant', 'getLeaseByProperty')->get();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.edit', compact('leaserenewal', 'newleases'));
    }

    public function update(PropertyLeaseRenewalRequest $request, $Id)
    {

        $this->authorize(PermissionEnum::PropertyLeaseRenewalUpdate, PropertyLeaseRenewal::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $leaserenewals = PropertyLeaseRenewal::findOrFail($Id);

            $lease = PropertyNewLease::findOrFail($Id);
            $leaserenewals->update([
                $lease->Id,
                'TenantId' => $validated['TenantId'],
                'PropertyId' => $validated['PropertyId'],
                'PaymentFrequency' => $validated['PaymentFrequency'],
                'EndDateCurrentLease' => $validated['EndDateCurrentLease'],
                'NewStartDate' => $validated['NewStartDate'],
                'NewEndDate' => $validated['NewEndDate'],
                'NewMonthlyRent' => $validated['NewMonthlyRent'],
                'Remarks' => $validated['Remarks'],
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($leaserenewals)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Lease Renewal');

            return redirect()->route('renewlease.index')->with('success', 'Lease renewal updated successfully');
        } catch (Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update Lease Renewal:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update Lease Renewal'])->withInput();
        }
    }

    public function destroy($id)
    {
        //Check if user has permission to delete property categories
        $this->authorize(PermissionEnum::PropertyLeaseRenewalDelete, PropertyLeaseRenewal::class);
        try {
            $leaserenewals = PropertyLeaseRenewal::findOrFail($id);
            $leaserenewals->delete();

            return redirect()->route('renewlease.index')
                ->with('success', 'Lease Renewal Deleted Successfully!');
        } catch (Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Lease Renewal: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Lease Renewal. Please try again.'])
                ->withInput();
        }
    }

}
