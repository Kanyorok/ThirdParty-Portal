<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyTenantClearance;
use App\Models\PropertyManagement\PropertyNewTenant;

class PropertyTenantClearanceController extends Controller
{
    //
    public function index()
    {
        $clearancetenants = PropertyTenantClearance::all();
        return view('property.tenantmanagement.tenantclearance.index', compact('clearancetenants'));
    }

    public function create(){
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.tenantclearance.create', compact('newtenants'));
    }

    public function show($id)
    {
        $clearancetenant = PropertyTenantClearance::find($id);
        return view('property.tenantmanagement.tenantclearance.show', compact('clearancetenant'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'Tenant' => 'required|string|max:50',
            'ExitDate' => 'required|date|max:50',
            'FinalInspection' => 'required|string|max:50',
            'AllDuesPaid' => 'required|string|max:50',
            'KeysReturned' => 'required|string|max:100',
            'DepositRefunded' => 'required|string|max:50',
            'AdditionalNotes' => 'required|string|max:50',
        ]);
        //dd('validation passed');
        $clearancetenant = PropertyTenantClearance::create([
            'Tenant' => $request->Tenant,
            'ExitDate' => $request->ExitDate,
            'FinalInspection' => $request->FinalInspection,
            'AllDuesPaid' => $request->AllDuesPaid,
            'KeysReturned' => $request->KeysReturned,
            'DepositRefunded' => $request->DepositRefunded,
            'AdditionalNotes' => $request->AdditionalNotes,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd('validation passed');
        return redirect()->route('tenantclearance.index')->with('success', 'Tenant created successfully');

    }

}
