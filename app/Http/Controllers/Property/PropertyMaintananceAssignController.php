<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Enums\Core\PermissionEnum;
use App\Http\Requests\Property\MaintenanceAndIssues\PropertyMaintenanceAssignRequest;
use App\Services\Property\MaintenanceAndIssues\PropertyMaintenanceAssignService;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;
use App\Models\HRM\Employee;
use App\Models\ThirdParies\Supplier;
use App\Models\Core\CodeDetail;

class PropertyMaintananceAssignController extends Controller
{
    //
    public function index()
    {
        $assignments = PropertyMaintenanceAssign::all();
        return view('property.maintenanceandissues.assignrequests.index', compact('assignments'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignCreate, PropertyMaintenanceAssign::class);
        $maintenancerequests = PropertyMaintenanceRequest::all();
        $employees = Employee::all();
        $suppliers = Supplier::all();
        $assignmentTypes = CodeDetail::where('CodeID', 'AssignmentType')->get();
        $priorityLevels = CodeDetail::where('CodeID','PriorityLevel')->get();
        return view('property.maintenanceandissues.assignrequests.create', compact('maintenancerequests', 'employees', 'suppliers', 'priorityLevels','assignmentTypes'));
    }

    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignView, PropertyMaintenanceAssign::class);
        $assignment = PropertyMaintenanceAssign::with('request')->findOrFail($Id);
        return view('property.maintenanceandissues.assignrequests.show', compact('assignment'));
    }

    public function store(PropertyMaintenanceAssignRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignCreate, PropertyMaintenanceAssign::class);

        $validated = $request->validated();

        $RequestNumber = PropertyMaintenanceRequest::findOrFail($validated['RequestNumber']);
        $AssignmentType = CodeDetail::findOrFail($validated['AssignmentType']);
        $PriorityLevel = CodeDetail::findOrFail($validated['PriorityLevel']);
        $PrequalifiedVendor = $validated['PrequalifiedVendor'] ?? null;
        $PrequalifiedVendor = $PrequalifiedVendor ? Supplier::findOrFail($PrequalifiedVendor) : null;
        $InternalTechnician = $validated['InternalTechnician'] ?? null;
        $InternalTechnician = $InternalTechnician ? Employee::findOrFail($InternalTechnician) : null;

        // Convert date strings to DateTime objects
        $assignmentDate = new \DateTime($validated['AssignmentDate']);
        $expectedStartDate = new \DateTime($validated['ExpectedStartDate']);
        $expectedCompletion = new \DateTime($validated['ExpectedCompletion']);

        $assignment = PropertyMaintenanceAssignService::create(
            $RequestNumber,
            $assignmentDate,
            $AssignmentType,
            $InternalTechnician,
            $PrequalifiedVendor,
            $expectedStartDate,
            $expectedCompletion,
            $PriorityLevel,
            $validated['InstructionNotes'],
            auth()->user()
        );

        return redirect()->route('assignrequest.index')->with('success', 'Assignment created successfully');
    }
    public function edit($Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignView, PropertyMaintenanceAssign::class);
        $assignment = PropertyMaintenanceAssign::with('request')->findOrFail($Id);
        $assignmentTypes = CodeDetail::where('CodeID', 'AssignmentType')->get();
        $priorityLevels = CodeDetail::where('CodeID','PriorityLevel')->get();
        $technicians = Employee::all();
        $vendors = Supplier::all();
        return view('property.maintenanceandissues.assignrequests.edit', compact('assignment','assignmentTypes', 'priorityLevels', 'technicians', 'vendors'));
    }

    public function update(PropertyMaintenanceAssignRequest $request, $Id)
    {
        $assignment = PropertyMaintenanceAssign::findOrFail($Id);

        $validated = $request->validated();

        // Convert date strings to DateTime objects
        $assignmentDate = new \DateTime($validated['AssignmentDate']);
        $expectedStartDate = new \DateTime($validated['ExpectedStartDate']);
        $expectedCompletion = new \DateTime($validated['ExpectedCompletion']);

        // Resolve full model instances from IDs
        $assignmentType = CodeDetail::findOrFail($validated['AssignmentType']);
        $priorityLevel = CodeDetail::findOrFail($validated['PriorityLevel']);

        $internalTechnician = $validated['InternalTechnician'] ?? null;
        $internalTechnician = $internalTechnician ? Employee::findOrFail($internalTechnician) : null;

        $prequalifiedVendor = $validated['PrequalifiedVendor'] ?? null;
        $prequalifiedVendor = $prequalifiedVendor ? Supplier::findOrFail($prequalifiedVendor) : null;

        // Call the service to update
        $service = new PropertyMaintenanceAssignService($assignment);

        $service->update(
            $assignmentDate,
            $assignmentType,
            $internalTechnician,
            $prequalifiedVendor,
            $expectedStartDate,
            $expectedCompletion,
            $priorityLevel,
            $validated['InstructionNotes'] ?? '',
            auth()->user()
        );

        return redirect()->route('assignrequest.index')->with('success', 'Assignment updated successfully.');
    }



    public function destroy($id)
    {
        //Check if user has permission to delete property categories
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignDelete, PropertyMaintenanceAssign::class);
        try {
            $assignment = PropertyMaintenanceAssign::findOrFail($id);
            $assignment->delete();

            return redirect()->route('assignrequest.index')
                ->with('success', 'Property Assignment Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property assignment: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Maintenance Assignment. Please try again.'])
                ->withInput();
        }
    }

}


