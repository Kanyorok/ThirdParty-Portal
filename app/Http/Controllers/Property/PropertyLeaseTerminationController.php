<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyLeaseTerminationRequest;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyNewLease;
use App\Services\Property\TenantAndLease\PropertyLeaseTerminationService;

class PropertyLeaseTerminationController extends Controller
{

    protected $service;

    public function __construct(PropertyLeaseTerminationService $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        $leaseterminations = PropertyLeaseTermination::with('lease', 'lease.tenant', 'code')->get();
        return view('property.tenantmanagement.leasemanagement.leasetermination.index', compact('leaseterminations'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::PropertyLeaseTerminationCreate, PropertyLeaseTermination::class);
        $newtenants = PropertyNewLease::where('IsActive', '1')->get();
        $terminationReasons = CodeDetail::where('CodeID', 'TerminationReason')->get();
        return view('property.tenantmanagement.leasemanagement.leasetermination.create', compact('newtenants', 'terminationReasons'));
    }


    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyLeaseTerminationView, PropertyLeaseTermination::class);
        $leasetermination = PropertyLeaseTermination::with('lease', 'lease.tenant', 'code')->findOrFail($Id);
        return view('property.tenantmanagement.leasemanagement.leasetermination.show', compact('leasetermination'));
    }

    public function store(PropertyLeaseTerminationRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyLeaseTerminationCreate, PropertyLeaseTermination::class);
        $validatedData = $request->validated();
        $LeaseID = PropertyNewLease::findOrFail($validatedData['LeaseID']);
        $TerminationReason = CodeDetail::findOrFail($validatedData['TerminationReason']);
        $document = $request->file('Document');
        $this->service->create(
            $LeaseID,
            $TerminationDate = $validatedData['TerminationDate'],
            $TerminationReason,
            $Remarks = $validatedData['Remarks'] ?? '',
            $request->user(),
            $document
        );
        return redirect()->route('terminatelease.index')->with('success', 'Lease termination created successfully');
    }

}
