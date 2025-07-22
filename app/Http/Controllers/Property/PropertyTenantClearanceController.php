<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Enums\Property\TenantClearanceEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyTenantClearanceRequest;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyLeaseTermination;
use App\Models\PropertyManagement\PropertyTenantClearance;
use App\Services\Property\TenantAndLease\PropertyTenantClearanceService;
use Carbon\Carbon;

class PropertyTenantClearanceController extends Controller
{

    protected $service;

    public function __construct(PropertyTenantClearanceService $service)
    {
        $this->service = $service;
    }
    //
    public function index()
    {
        $clearancetenants = PropertyTenantClearance::all();
        return view('property.tenantmanagement.tenantclearance.index', compact('clearancetenants'));
    }

    public function create(){
        $this->authorize(PermissionEnum::TenantMentenanceCreate, PropertyTenantClearance::class);
        $newtenants = PropertyLeaseTermination::whereNotIn('LeaseID',PropertyTenantClearance::pluck('LeaseId'))
        ->with('lease.tenant','code')->get();
        $codedetails = CodeDetail::where('CodeID', 'DepositRefunded')->get();
        return view('property.tenantmanagement.tenantclearance.create', compact('newtenants', 'codedetails'));
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::TenantMentenanceView, PropertyTenantClearance::class);
        $clearancetenant = PropertyTenantClearance::find($Id);
        return view('property.tenantmanagement.tenantclearance.show', compact('clearancetenant'));
    }

    public function store(PropertyTenantClearanceRequest $request)
    {
        $this->authorize(PermissionEnum::TenantMentenanceCreate, PropertyTenantClearance::class);

        $validatedData = $request->validated();
        $statusEnum = TenantClearanceEnum::from($validatedData['Status']);
        $lease = PropertyLeaseTermination::where('LeaseID', $validatedData['LeaseId'])->firstOrFail();
        $depositRefunded = $validatedData['DepositRefunded']
            ? CodeDetail::findOrFail($validatedData['DepositRefunded'])
            : null;

        $clearance = $this->service->create(
            $lease,
            \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['ExitDate']),
            $validatedData['FinalInspection'],
            $validatedData['AllDuesPaid'],
            $validatedData['KeysReturned'],
            $depositRefunded,
            $validatedData['AdditionalNotes'],
            $statusEnum,
            $request->user()
        );

        return redirect()->route('tenantclearance.index')->with('success', 'Tenant lease clearance created successfully');
    }


    public function edit($Id)
    {
        $this->authorize(PermissionEnum::PropertyCategoryUpdate, PropertyTenantClearance::class);
        $clearancetenant = PropertyTenantClearance::with('lease')->get()->find($Id);
        $codedetails = CodeDetail::where('CodeID', 'DepositRefunded')->get();
        return view('property.tenantmanagement.tenantclearance.edit', compact('clearancetenant', 'codedetails'));
    }

    public function update(PropertyTenantClearanceRequest $request, $Id)
    {
        $this->authorize(PermissionEnum::PropertyCategoryUpdate, PropertyTenantClearance::class);
        $validatedData = $request->validated();
        $statusEnum = TenantClearanceEnum::from($validatedData['Status']);
        $depositRefunded = $validatedData['DepositRefunded'] ? CodeDetail::findOrFail($validatedData['DepositRefunded']) : null;
        $LeaseId = PropertyTenantClearance::where('Id', $Id)->firstOrFail();
        $this->service->update(
            $LeaseId,
            $exitdate = \Carbon\Carbon::createFromFormat('d/m/Y', $validatedData['ExitDate']),
            $validatedData['FinalInspection'],
            $validatedData['AllDuesPaid'],
            $validatedData['KeysReturned'],
            $depositRefunded,
            $validatedData['AdditionalNotes'],
            $statusEnum,
            $request->user()
        );
        return redirect()->route('tenantclearance.index')->with('success', 'Tenant Clearance updated successfully');
    }
}
