<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Enums\Core\PermissionEnum;
use App\Http\Requests\Property\MaintenanceAndIssues\PropertyMaintenanceAssignRequest;
use App\Services\Property\MaintenanceAndIssues\PropertyMaintenanceAssignService;
use Illuminate\Http\Request;
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
        $assignment = PropertyMaintenanceAssign::findOrFail($Id);
        return view('property.maintenanceandissues.assignrequests.show', compact('assignment'));
    }

    public function store(PropertyMaintenanceAssignRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignCreate, PropertyMaintenanceAssign::class);
        //dd($request->all());

        $validated = $request->validated();

      // dd('validation');

        $assignment = PropertyMaintenanceAssign::create([
            'RequestNumber' => $validated['RequestNumber'],
            'Property' => $validated['Property'],
            'Block' => $validated['Block'],
            'Floor' => $validated['Floor'],
            'Unit' => $validated['Unit'],
            'AssignmentDate' => $validated['AssignmentDate'],
            'AssignmentType' => $validated['AssignmentType'],
            'InternalTechnician' => $validated['InternalTechnician'] ?? null,
            'PrequalifiedVendor' => $validated['PrequalifiedVendor'] ?? null,
            'ExpectedStartDate' => $validated['ExpectedStartDate'],
            'ExpectedCompletion' => $validated['ExpectedCompletion'],
            'PriorityLevel' => $validated['PriorityLevel'],
            'InstructionNotes' => $validated['InstructionNotes'],
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($assignment)
            ->event('create')
            ->log("Created Property Assignment #{$assignment->Id}.");

        return redirect()->route('assignrequest.index')->with('success', 'Assignment created successfully');
    }
    public function edit($Id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignUpdate, PropertyMaintenanceAssign::class);
        $maintenancerequests = PropertyMaintenanceRequest::all();
        $assignment = PropertyMaintenanceAssign::findOrFail($Id);
        $employees = Employee::all();
        $suppliers = Supplier::all();
        $assignmentTypes = CodeDetail::where('CodeID', 'AssignmentType')->get();
        $priorityLevels = CodeDetail::where('CodeID','PriorityLevel')->get();
        return view('property.maintenanceandissues.assignrequests.edit', compact('maintenancerequests', 'employees', 'suppliers', 'assignmentTypes', 'priorityLevels', 'assignment'));
    }

    public function update(Request $request, $Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignUpdate, PropertyMaintenanceAssign::class);
        $validated = $request->validate([
           'RequestNumber' => 'required|exists:t_MaintenanceRequest,Id',
            'Property' => 'required|string|max:100',
            'Block' => 'required|string|max:100',
            'Floor' => 'required|string|max:100',
            'Unit' => 'required|string|max:100',
            'AssignmentDate' => 'required|date',
            'AssignmentType' => 'required|exists:t_CodeDetails,Id',
            'InternalTechnician' => 'nullable|exists:t_Employees,Id',
            'PrequalifiedVendor' => 'nullable|exists:t_Suppliers,Id',
            'ExpectedStartDate' => 'required|date',
            'ExpectedCompletion' => 'required|date',
            'PriorityLevel' => 'required|exists:t_CodeDetails,Id',
            'InstructionNotes' => 'nullable|string|max:100',
   ]);

        DB::beginTransaction();

        try {
            $assignment = PropertyMaintenanceAssign::findOrFail($Id);

            $assignment->update([
            'RequestNumber' => $validated['RequestNumber'],
            'Property' => $validated['Property'],
            'Block' => $validated['Block'],
            'Floor' => $validated['Floor'],
            'Unit' => $validated['Unit'],
            'AssignmentDate' => $validated['AssignmentDate'],
            'AssignmentType' => $validated['AssignmentType'],
            'InternalTechnician' => $validated['InternalTechnician'] ?? null,
            'PrequalifiedVendor' => $validated['PrequalifiedVendor'] ?? null,
            'ExpectedStartDate' => $validated['ExpectedStartDate'],
            'ExpectedCompletion' => $validated['ExpectedCompletion'],
            'PriorityLevel' => $validated['PriorityLevel'],
            'InstructionNotes' => $validated['InstructionNotes'],
            'CreatedBy' => Auth::Id(),
            'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($assignment)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Assignment');

            return redirect()->route('assignrequest.index')->with('success', 'Assignment updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update property assignment:' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update property assignment'])->withInput();
        }
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


