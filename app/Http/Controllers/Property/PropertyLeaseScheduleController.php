<?php

namespace App\Http\Controllers\Property;

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

}
