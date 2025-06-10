<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyLeaseRenewal;
use App\Models\PropertyManagement\PropertyNewTenant;

class PropertyLeaseRenewalController extends Controller

{
    //
    public function index()
    {
        $leaserenewals = PropertyLeaseRenewal::all();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.index', compact('leaserenewals'));
    }

    public function create(){
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.leasemanagement.leaserenewal.create', compact('newtenants'));
    }

    public function show($id)
    {
        $leaserenewal = PropertyLeaseRenewal::findOrFail($id);
        return view('property.tenantmanagement.leasemanagement.leaserenewal.show', compact('leaserenewal'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'CurrentLease' => 'required|string|max:50',
            'EndDateCurrentLease' => 'required|date',
            'NewStartDate' => 'required|date',
            'NewEndDate' => 'required|date',
            'NewMonthlyRent' => 'required|integer',
            'PaymentFrequency' => 'required|string|max:50',
            'Remarks' => 'nullable|string|max:100',
        ]);
        //dd('validation');
        $leaserenewal = PropertyLeaseRenewal::create([
            'CurrentLease' => $request->CurrentLease,
            'EndDateCurrentLease' => $request->EndDateCurrentLease,
            'NewStartDate' => $request->NewStartDate,
            'NewEndDate' => $request->NewEndDate,
            'NewMonthlyRent' => $request->NewMonthlyRent,
            'PaymentFrequency' => $request->PaymentFrequency,
            'Remarks' => $request->Remarks,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        return redirect()->route('renewlease.index')->with('success', 'Lease renewal created successfully');
    }
}
