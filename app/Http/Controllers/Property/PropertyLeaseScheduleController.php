<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Enums\Core\PermissionEnum;
use App\Http\Requests\Property\TenantAndLease\PropertyLeaseScheduleRequest;
use App\Services\Property\TenantAndLease\PropertyLeaseScheduleService;
use Illuminate\Http\Request;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyNewLease;


class PropertyLeaseScheduleController extends Controller
{
    //
    public function index()
    {
        $leaseschedules = PropertyLeaseSchedule::with(['lease'])->get();
        return view('property.tenantmanagement.leasemanagement.leaseschedule.index', compact('leaseschedules'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyLeaseScheduleCreate, PropertyLeaseSchedule::class);

        // Get all LeaseNumber values already scheduled
        $scheduledLeaseIds = PropertyLeaseSchedule::pluck('LeaseNumber');

        // Get leases that are not scheduled
        $newleases = PropertyNewLease::with(['getPropertyByTenant', 'getLeaseByProperty'])
            ->whereNotIn('Id', $scheduledLeaseIds)
            ->get();

        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('property.tenantmanagement.leasemanagement.leaseschedule.create', compact('newleases', 'codes'));
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
        $this->authorize(PermissionEnum::PropertyLeaseScheduleView, PropertyLeaseSchedule::class);
        $leaseschedule = PropertyLeaseSchedule::find($id);
        return view('property.tenantmanagement.leasemanagement.leaseschedule.show', compact('leaseschedule'));
    }

public function store(PropertyLeaseScheduleRequest $request)
{
    try {
    $validated = $request->validated();
    $leaseId = (int) $validated['LeaseId'];
    $paymentFrequencyId = (int) $validated['PaymentFrequency'];

    // Use the lease ID to get the full lease
    $lease = PropertyNewLease::findOrFail($leaseId);
    PropertyLeaseScheduleService::create(
        $leaseId,                  // from DB
        $paymentFrequencyId,
        $validated['StartDate'],
        $validated['EndDate'],
        $validated['BaseRent'],
        $validated['ServiceCharge'],
        $validated['ParkingFee'],
        $validated['OtherCharges'],
        Auth::user()
    );

    return redirect()->route('schedulelease.index')->with('success', 'Lease schedule added!');
    } catch (\Exception $e) {
        // Redirect back with error message
        return redirect()->back()->with('error', $e->getMessage());
    }
}

    public function edit($id)
    {
        //Check if user has permission to edit tender categories
         $this->authorize(PermissionEnum::PropertyLeaseScheduleUpdate, PropertyLeaseSchedule::class);
        $leaseschedules = PropertyLeaseSchedule::findOrFail($id);
        $newtenants = PropertyNewTenant::all();
        $newleases = PropertyNewLease::all();
        $codes = CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('property.tenantmanagement.leasemanagement.leaseschedule.edit',compact('leaseschedules','newtenants','newleases','codes'));
    }
     public function update(Request $request, $id){

        $this->authorize(PermissionEnum::PropertyLeaseScheduleUpdate, PropertyLeaseSchedule::class);
        $validated=$request->validate([
            'LeaseId' => 'required|exists:t_LeaseCreation,Id',
            'PaymentFrequency' => 'required|string|max:50',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date',
            'BaseRent' => 'required|numeric',
            'ServiceCharge' => 'required|numeric',
            'ParkingFee' => 'required|numeric',
            'OtherCharges' => 'required|numeric',
        ]);

        DB::beginTransaction();

        try {
            $leaseschedules = PropertyLeaseSchedule::findOrFail($id);

            $leaseschedules->update([
                'PaymentFrequency' => $validated['PaymentFrequency'],
                'StartDate' => $validated['StartDate'],
                'EndDate' => $validated['EndDate'],
                'BaseRent' => $validated['BaseRent'],
                'ServiceCharge' => $validated['ServiceCharge'],
                'ParkingFee' => $validated['ParkingFee'],
                'OtherCharges' => $validated['OtherCharges'],
                'ModifiedBy' => Auth::Id(),
            ]);
 
        DB::commit();
        activity()
                ->performedOn($leaseschedules)
                ->causedBy(Auth::user())
                ->withProperties(['action'=>'update'])
                ->log('Updated Lease Schedule');

                return redirect()->route('schedulelease.index')->with('success' , 'Lease schedule updated successfully');
            }catch(\Throwable $th) {
                DB::rollBack();
                Log::error('Failed to Update Lease Schedule:' . $th->getMessage());

                return back()->withErrors(['error'=>'Failed to update Lease Schedule'])->withInput();
            }
       }
    public function destroy($id)
    {
        //Check if user has permission to delete property categories
        $this->authorize(PermissionEnum::PropertyLeaseScheduleDelete , PropertyLeaseSchedule::class);
        try {
            $leaseschedules = PropertyLeaseSchedule::findOrFail($id);
            $leaseschedules->delete();

            return redirect()->route('schedulelease.index')
                ->with('success', 'Lease Schedule Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting Lease Schedule: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Lease Schedule. Please try again.'])
                ->withInput();
        }
    }
    public function print($Id)
    {
        $leaseschedule = PropertyLeaseSchedule::with('lease.tenant', 'lease.property', 'paymentFrequency')
                        ->findOrFail($Id);

        return view('property.tenantmanagement.leasemanagement.leaseschedule.print', compact('leaseschedule'));
    }
   


}



