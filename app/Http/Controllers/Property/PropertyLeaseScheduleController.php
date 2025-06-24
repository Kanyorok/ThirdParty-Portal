<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyLeaseSchedule;
use App\Models\PropertyManagement\PropertyNewTenant;

class PropertyLeaseScheduleController extends Controller
{
    //
    public function index()
    {
        $leaseschedules = PropertyLeaseSchedule::all();
        return view('property.tenantmanagement.leasemanagement.leaseschedule.index', compact('leaseschedules'));
    }

    public function create()
    {
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.leasemanagement.leaseschedule.create', compact('newtenants'));
    }

    public function show($id)
    {
        $leaseschedule = PropertyLeaseSchedule::find($id);
        return view('property.tenantmanagement.leasemanagement.leaseschedule.show', compact('leaseschedule'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'leaseID' => 'required|string|max:100',
            'PaymentFrequency' => 'required|string|max:50',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date',
            'BaseRent' => 'required|numeric',
            'ServiceCharge' => 'required|numeric',
            'ParkingFee' => 'required|numeric',
            'OtherCharges' => 'required|numeric',
        ]);
        //dd('validation');
        $leaseschedule = PropertyLeaseSchedule::create([
            'leaseID' => $request->leaseID,
            'PaymentFrequency' => $request->PaymentFrequency,
            'StartDate' => $request->StartDate,
            'EndDate' => $request->EndDate,
            'BaseRent' => $request->BaseRent,
            'ServiceCharge' => $request->ServiceCharge,
            'ParkingFee' => $request->ParkingFee,
            'OtherCharges' => $request->OtherCharges,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd('Validation');
        return redirect()->route('schedulelease.index')->with('success', 'Lease schedule created successfully');
    }
    public function edit($id)
    {
        //Check if user has permission to edit tender categories
       // $this->authorize(PermissionEnum::PropertyTypeUpdate, PropertyType::class);
        $leaseschedules = PropertyLeaseSchedule::findOrFail($id);
        $newtenants = PropertyNewTenant::all();

        return view('property.tenantmanagement.leasemanagement.leaseschedule.edit',compact('leaseschedules','newtenants'));
    }
     public function update(Request $request, $id){ 
       // $this->authorize(PermissionEnum::PropertyTypeUpdate , PropertyType::class);
        $validated=$request->validate([
            'leaseID' => 'required|string|max:100',
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
                'leaseID' => $validated['leaseID'],
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
        //$this->authorize(PermissionEnum::PropertyTypeDelete , PropertyType::class);
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


}



