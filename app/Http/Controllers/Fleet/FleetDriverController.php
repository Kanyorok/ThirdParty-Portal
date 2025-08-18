<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Fleet\FleetDriver;
use App\Services\FleetManagement\FleetDriverService;
use App\Models\Core\CodeDetail;
use App\Http\Requests\FleetManagement\FleetDriverRequest;
use App\Models\HRM\Employee;

class FleetDriverController extends Controller
{
    protected $fleetDriverService;

    public function __construct(FleetDriverService $fleetDriverService)
    {
        $this->fleetDriverService = $fleetDriverService;
    }

     public function index()
    {
        $drivers = FleetDriver::with(['driver', 'employmentType'])
            ->where('CreatedBy', Auth::id())
            ->get();

        return view('fleet.drivers.index', compact('drivers'));
    }

    public function create()
    {
        $branchId = Auth::user()->employee?->BranchId;

        $excludedIds = FleetDriver::pluck('StaffNumber')->toArray();

        $employeesQuery = Employee::where('BranchId', $branchId);

        if (!empty($excludedIds)) {
            $employeesQuery->whereNotIn('Id', $excludedIds);
        }

        $staffNo = $employeesQuery
            ->orderBy('LastName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeID', 'Email', 'Phone']);

        $employmentType = CodeDetail::where('CodeID', 'EmploymentType')
            ->orderBy('Value')
            ->get();
            

        return view('fleet.drivers.create', compact('staffNo', 'employmentType'));
    }

    public function store(FleetDriverRequest $request)
    {
        $this->fleetDriverService->create($request->validated());
        return redirect()->route('fleet.drivers.index')
            ->with('success', 'Driver registered successfully.');
    }

    public function edit($id)
    {
        $driver = FleetDriver::findOrFail($id);
        $branchId = Auth::user()->employee?->BranchId;

        $excludedIds = FleetDriver::where('Id', '!=', $driver->Id)->pluck('StaffNumber')->toArray();

        $employeesQuery = Employee::where('BranchId', $branchId);

        if (!empty($excludedIds)) {
            $employeesQuery->whereNotIn('Id', $excludedIds);
        }

        $staffNo = $employeesQuery
            ->orderBy('LastName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeID', 'Email', 'Phone']);

        $employmentType = CodeDetail::where('CodeID', 'EmploymentType')
            ->orderBy('Value')
            ->get();

        return view('fleet.drivers.edit', compact('driver', 'staffNo', 'employmentType'));
    }

    public function update(FleetDriverRequest $request, $id)
    {
        $this->fleetDriverService->update($id, $request->validated());
        return redirect()->route('fleet.drivers.index')
            ->with('success', 'Driver details updated successfully.');
    }

    public function destroy($id)
    {
        $this->fleetDriverService->delete($id);
        return redirect()->route('fleet.drivers.index')
            ->with('success', 'Driver deactivated successfully.');
    }

    public function show($id)
{
    $driver = FleetDriver::with(['driver', 'employmentType'])
        ->where('CreatedBy', Auth::id())
        ->findOrFail($id);

    return view('fleet.drivers.show', compact('driver'));
}


}
