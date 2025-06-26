<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyLeaseTerminationRequest;
use App\Services\Property\TenantAndLease\PropertyLeaseTerminationService;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewTenant;

class PropertyLeaseTerminationController extends Controller
{
    
    protected $service;

    public function __construct(PropertyLeaseTerminationService $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        $leaseterminations = PropertyLeaseTermination::all();
        return view('property.tenantmanagement.leasemanagement.leasetermination.index', compact('leaseterminations'));
    }

    public function create(){
        $newtenants = PropertyNewTenant::all();
        return view('property.tenantmanagement.leasemanagement.leasetermination.create', compact('newtenants'));
    }

    public function show($Id)
    {
        $leasetermination = PropertyLeaseTermination::findOrFail($Id);
        return view('property.tenantmanagement.leasemanagement.leasetermination.show', compact('leasetermination'));
    }

    public function store(PropertyLeaseTerminationRequest $request)
    {

        $validatedData = $request->validated();
        $LeaseID = $validatedData['LeaseID'];
        $TerminationReason = $validatedData['TerminationReason'];
        $this->service->create(
            $LeaseID,
            $TerminationDate = $validatedData['TerminationDate'],
            $TerminationReason,
            $Remarks = $validatedData['Remarks'],
            $request->user()
        );  
        return redirect()->route('terminatelease.index')->with('success', 'Lease termination created successfully');
    }

}
