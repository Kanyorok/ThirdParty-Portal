<?php

namespace App\Http\Controllers\Property;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\TenantAndLease\PropertyNewTenantRequest;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyNewTenant;
use App\Services\Property\TenantAndLease\PropertyNewTenantService;

class PropertyNewTenantController extends Controller
{
    protected $service;

    public function __construct(PropertyNewTenantService $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        $newtenants = PropertyNewTenant::with('type')->get();
        return view('property.tenantmanagement.tenantmaintenance.index', compact('newtenants'));
    }

    public function create(){
      //  $this->authorize(PermissionEnum::TenantMentenanceCreate, PropertyNewTenant::class);
        $tenantTypes = CodeDetail::where('CodeID', 'TenantType')->get();
        return view('property.tenantmanagement.tenantmaintenance.create', compact('tenantTypes'));
    }

    public function edit($id)
    {
      //  $this->authorize(PermissionEnum::TenantMentenanceUpdate, PropertyNewTenant::class);

        $newtenant = PropertyNewTenant::findOrFail($id);
        $tenantTypes = CodeDetail::where('CodeID', 'TenantType')->get();

        return view('property.tenantmanagement.tenantmaintenance.edit', compact('newtenant', 'tenantTypes'));
    }

    public function show($id)
    {
       // $this->authorize(PermissionEnum::TenantMentenanceView, PropertyNewTenant::class);
        $newtenant = PropertyNewTenant::findOrFail($id);
        return view('property.tenantmanagement.tenantmaintenance.show', compact('newtenant'));
    }

    public function store(PropertyNewTenantRequest $request)
    {
      //  $this->authorize(PermissionEnum::TenantMentenanceCreate, PropertyNewTenant::class);

        $data = $request->validated();

        $tenantTypeModel = CodeDetail::findOrFail($data['TenantType']);
        $document = $request->file('Document');

        $this->service::create(
            $tenantTypeModel,
            $data['TenantName'],
            $data['IDRegistrationNo'],
            $data['PhoneNumber'],
            $data['EmailAddress'],
            $data['Nationality'],
            $data['PostalAddress'],
            $data['Remarks'] ?? '',
            $data['IsActive'],
            $request->user(),
            $document
        );

        return redirect()->route('addtenant.index')->with('success', 'Tenant created successfully');

    }

    public function update(PropertyNewTenantRequest $request, $id)
    {
      //  $this->authorize(PermissionEnum::TenantMentenanceUpdate, PropertyNewTenant::class);
        $data = $request->validated();

        $newtenant = PropertyNewTenant::findOrFail($id);

        $tenantTypeModel = CodeDetail::findOrFail($data['TenantType']);

        $this->service::update(
            $newtenant,
            $tenantTypeModel,
            $data['TenantName'],
            $data['IDRegistrationNo'],
            $data['PhoneNumber'],
            $data['EmailAddress'],
            $data['Nationality'],
            $data['PostalAddress'],
            $data['Remarks'] ?? '',
            $data['IsActive'],
            $request->user()
        );


        return redirect()->route('addtenant.index')->with('success', 'Tenant updated successfully.');
    }

}
