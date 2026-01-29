<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyNewTenantRequest;
use App\Models\Core\Approval\CodeDetail;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Models\ThirdParty\ThirdParties;

class PropertyNewTenantController extends Controller
{
    protected $service;

    public function index()
    {
        $newtenants = PropertyNewTenant::with('type', 'thirdParty')->get();

        return view('property.tenantmanagement.tenantmaintenance.index', compact('newtenants'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::TenantMaintenanceCreate, PropertyNewTenant::class);

        $tenantTypes = CodeDetail::where('CodeID', 'TenantType')->get();

        // Already assigned tenants
        $assignedTenantIds = PropertyNewTenant::pluck('ThirdPartyId')->toArray();

        // // Fetch only tenants with active Tenant type

        $tenants = ThirdParties::all();

        return view('property.tenantmanagement.tenantmaintenance.create', compact('tenantTypes', 'tenants'));
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::TenantMaintenanceUpdate, PropertyNewTenant::class);
        $tenants = PropertyNewTenant::with('type', 'thirdParty')->findOrFail($id);
        $tenantTypes = CodeDetail::where('CodeID', 'TenantType')->get();

        return view('property.tenantmanagement.tenantmaintenance.edit', compact('tenants', 'tenantTypes'));
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::TenantMaintenanceView, PropertyNewTenant::class);
        $newtenant = PropertyNewTenant::findOrFail($id);

        return view('property.tenantmanagement.tenantmaintenance.show', compact('newtenant'));
    }

    public function store(PropertyNewTenantRequest $request)
    {
        $this->authorize(PermissionEnum::TenantMaintenanceCreate, PropertyNewTenant::class);

        $data = $request->validated();

        $thirdpartyId = ThirdParties::findOrFail($data['ThirdPartyId']);
        $tenantTypeModel = CodeDetail::findOrFail($data['TenantType']);
        $document = $request->file('Document');

        $this->service::create(
            $thirdpartyId,
            $tenantTypeModel,
            $data['Remarks'] ?? '',
            $data['IsActive'],
            $request->user(),
            $document
        );

        return redirect()->route('addtenant.index')->with('success', 'Tenant created successfully');
    }

    public function update(PropertyNewTenantRequest $request, $id)
    {
        $this->authorize(PermissionEnum::TenantMaintenanceUpdate, PropertyNewTenant::class);

        $data = $request->validated();
        $newtenant = PropertyNewTenant::findOrFail($id);

        $tenantTypeModel = CodeDetail::findOrFail($data['TenantType']);
        $document = $request->file('Document');

        $this->service::update(
            $newtenant,
            $tenantTypeModel,
            $data['Remarks'] ?? '',
            $data['IsActive'],
            $request->user(),
            $document
        );

        return redirect()
            ->route('addtenant.index')
            ->with('success', 'Tenant updated successfully.');
    }
}
