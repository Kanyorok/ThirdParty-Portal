<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewTenant;

class PropertyLeaseTerminationController extends Controller
{
    //
    public function index()
    {
        $leaseterminations = PropertyLeaseTermination::all();
        return view('property.tenantmanagement.leasemanagement.leasetermination.index', compact('leaseterminations'));
    }

    public function create(){
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.leasemanagement.leasetermination.create', compact('newtenants'));
    }
    public function show($id)
    {
        $leasetermination = PropertyLeaseTermination::findOrFail($id);
        return view('property.tenantmanagement.leasemanagement.leasetermination.show', compact('leasetermination'));
    }
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'LeaseID'=>'required|string|max:50',
            'TerminationDate'=>'required|date',
            'TerminationReason'=>'required|string|max:100',
            'Remarks'=>'nullable|string|max:100',
        ]);
           //dd('validation');
         $leasetermination = PropertyLeaseTermination::create([
            'LeaseID'=> $request->LeaseID,
            'TerminationDate'=> $request->TerminationDate,
            'TerminationReason'=> $request->TerminationReason,
            'Remarks'=> $request->Remarks,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd('Validation');
           return redirect()->route('terminatelease.index')->with('success','Lease termination created successfully');
    }

}
