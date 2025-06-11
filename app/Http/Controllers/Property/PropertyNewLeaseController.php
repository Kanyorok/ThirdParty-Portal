<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\PropertyManagement\PropertyUnit;

class PropertyNewLeaseController extends Controller
{
    //
    public function index()
    {
        $newleases = PropertyNewLease::all();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.index', compact('newleases'));
    }

    public function create(){
        $units = PropertyUnit::all();
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.create', compact('newtenants', 'units'));
    }

    public function show($id)
    {
        $newlease = PropertyNewLease::findOrFail($id);
        return view('property.tenantmanagement.leasemanagement.leasemaintenance.show', compact('newlease'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'Tenant' => 'required|string|max:50',
            'PropertyID' => 'required|string|max:50',
            'BlockID' => 'required|string|max:50',
            'FloorID' => 'required|string|max:50',
            'Unit' => 'required|string|max:100',
            'StartDate' => 'required|date',
            'EndDate' => 'required|date',
            'PaymentFrequency' => 'required|string|max:50',
            'MonthlyRent' => 'required|integer',
            'Deposit' => 'required|integer',
            'DueDay' => 'required|integer',
            'SpecialTerms' => 'nullable|string|max:255',
        ]);
        //dd('validation passed');
        $newlease = PropertyNewLease::create([
            'Tenant' => $request->Tenant,
            'PropertyID' => $request->PropertyID,
            'BlockID' => $request->BlockID,
            'FloorID' => $request->FloorID,
            'Unit' => $request->Unit,
            'StartDate' => $request->StartDate,
            'EndDate' => $request->EndDate,
            'PaymentFrequency' => $request->PaymentFrequency,
            'MonthlyRent' => $request->MonthlyRent,
            'Deposit' => $request->Deposit,
            'DueDay' => $request->DueDay,
            'SpecialTerms' => $request->SpecialTerms,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd('validation passed');
        return redirect()->route('addlease.index')->with('success', 'Lease created successfully');

    }


}
