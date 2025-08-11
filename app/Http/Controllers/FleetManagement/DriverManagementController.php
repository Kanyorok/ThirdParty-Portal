<?php

namespace App\Http\Controllers\FleetManagement;

use App\Http\Controllers\Controller;
use App\Services\FleetManagement\DriverManagementService;
use App\Models\FleetManagement\DriverManagement;
use App\Policies\FleetManagement\DriverManagementPolicy;
use App\Http\Requests\FleetManagement\DriverManagementRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Core\CodeDetail;

class DriverManagementController extends Controller
{
    protected DriverManagementService $service;

    public function __construct(DriverManagementService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $this->authorize('viewAny', DriverManagement::class);
        $drivers = DriverManagement::all();
        return view("fleetmanagement.drivermanagement.index", compact('drivers'));
    }

   public function create()
{
    $this->authorize('create', DriverManagement::class);
    $employmentStatus = CodeDetail::where('CodeID', 'EmploymentStatus')
        ->orderBy('Value')
        ->get();

    return view("fleetmanagement.drivermanagement.create", compact('employmentStatus'));
}
    public function store(DriverManagementRequest $request)
{
    $this->authorize('create', DriverManagement::class);
    if (DriverManagement::where('LicenseNumber', $request->LicenseNumber)->exists()) {
        return back()
            ->withErrors(['LicenseNumber' => "The driver's license already exists."])
            ->withInput();
    }

    $validated = $request->validated();
    $driver = $this->service->createDriver($validated);

    return redirect()
        ->route("drivermanagement.index")
        ->with('success', 'Driver created successfully.');
}


    public function show($id)
    {
        $this->authorize('view', DriverManagement::class);
        $driver = DriverManagement::findOrFail($id);
        return view("fleetmanagement.drivermanagement.show", compact('driver'));
    }

   public function edit($Id)
    {
        $this->authorize('update', DriverManagement::class);
        $driver = DriverManagement::findOrFail($Id);

        $employmentStatus = CodeDetail::where('CodeID', 'EmploymentStatus')
            ->orderBy('Value')
            ->get();

        return view('fleetmanagement.drivermanagement.edit', compact('driver', 'employmentStatus'));
    }


      public function update(DriverManagementRequest $request, $Id)
    {
        $this->authorize('update', DriverManagement::class);

        $validated = $request->validated();
        $driver = DriverManagement::findOrFail($Id);
        $this->service->updateDriver($driver, $validated);
        
        return redirect()
            ->route("drivermanagement.index")
            ->with('success', 'Driver updated successfully.');
    }


    public function destroy($id)
    {
        $this->authorize('destroy', DriverManagement::class);
        $driver = DriverManagement::findOrFail($id);
        $this->service->deleteDriver($driver);

        return redirect()
            ->route("drivermanagement.index")
            ->with('success', 'Driver deleted successfully.');
    }
}