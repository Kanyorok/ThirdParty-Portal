<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyNewTenant;

class PropertyNewTenantController extends Controller
{
    //
    public function index()
    {
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.tenantmaintenance.index', compact('newtenants'));
    }

    public function create(){
        return view('property.tenantmanagement.tenantmaintenance.create');
    }

    public function edit(){
        return view('property.tenantmanagement.tenantmaintenance.edit');
    }
    public function show($id)
    {
        $newtenant = PropertyNewTenant::findOrFail($id);
        return view('property.tenantmanagement.tenantmaintenance.show', compact('newtenant'));
    }
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'TenantType'=>'required|string|max:50',
            'TenantName'=>'required|string|max:100',
            'IDRegistrationNo'=>'required|string|max:50',
            'PhoneNumber'=>'required|string|max:50',
            'EmailAddress'=>'required|string|max:100',
            'Nationality'=>'required|string|max:50',
            'PostalAddress'=>'required|string|max:50',
            'Remarks'=>'required|string|max:255',
        ]);
       //dd('validation passed');
         $newtenant = PropertyNewTenant::create([
            'TenantType'=> $request->TenantType,
            'TenantName'=> $request->TenantName,
            'IDRegistrationNo'=> $request->IDRegistrationNo,
            'PhoneNumber'=> $request->PhoneNumber,
            'EmailAddress'=> $request->EmailAddress,
            'Nationality'=> $request->Nationality,
            'PostalAddress'=> $request->PostalAddress,
            'Remarks'=> $request->Remarks,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
         //dd('validation passed');
           return redirect()->route('addtenant.index')->with('success','Tenant created successfully');
          
    }
}
