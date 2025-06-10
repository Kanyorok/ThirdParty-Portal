<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;

class PropertyMaintananceAssignController extends Controller
{
    //
    public function index()
    {
        $assignments = PropertyMaintenanceAssign::all();
        return view('property.maintenanceandissues.assignrequests.index', compact('assignments'));
    }

    public function create(){
        $maintenancerequests = PropertyMaintenanceRequest::all();
        return view('property.maintenanceandissues.assignrequests.create', compact('maintenancerequests'));
    }
    public function show($id)
    {
        $assignment = PropertyMaintenanceAssign::findOrFail($id);
        return view('property.maintenanceandissues.assignrequests.show', compact('assignment'));
    }
    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'Property'=>'required|string|max:50',
            'Block'=>'required|string|max:50',
            'Floor'=>'required|string|max:50',
            'Unit'=>'required|string|max:50',
            'IssueDescription'=>'required|string|max:255',
            'AssignmentDate'=>'required|date',
            'AssignmentType'=>'required|string|max:50',
            'InternalTechnician'=>'required|string|max:50',
            'PrequalifiedVendor'=>'required|string|max:50',
            'ExpectedStartDate'=>'required|date',
            'ExpectedCompletion'=>'required|date',
            'PriorityLevel'=>'required|string|max:50',
            'InstructionNotes'=>'required|string|max:100',
        ]);
           //dd('validation');
         $assignment = PropertyMaintenanceAssign::create([
            'Property'=> $request->Property,
            'Block'=> $request->Block,
            'Floor'=> $request->Floor,
            'Unit'=> $request->Unit,
            'IssueDescription'=> $request->IssueDescription,
            'AssignmentDate'=> $request->AssignmentDate,
            'AssignmentType'=> $request->AssignmentType,
            'InternalTechnician'=> $request->InternalTechnician,
            'PrequalifiedVendor'=> $request->PrequalifiedVendor,
            'ExpectedStartDate'=> $request->ExpectedStartDate,
            'ExpectedCompletion'=> $request->ExpectedCompletion,
            'PriorityLevel'=> $request->PriorityLevel,
            'InstructionNotes'=> $request->InstructionNotes,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
           return redirect()->route('assignrequest.index')->with('success','Lease renewal created successfully');
    }
}
