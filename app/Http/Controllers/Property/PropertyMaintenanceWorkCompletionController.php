<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;

class PropertyMaintenanceWorkCompletionController extends Controller
{
    //
    public function index()
    {
        @$workCompletions = PropertyMaintenanceWorkCompletion::all();
        return view('property.maintenanceandissues.workcompletion.index', compact('workCompletions'));
    }

    public function create(){
        $maintenancerequests = PropertyMaintenanceRequest::all();
        return view('property.maintenanceandissues.workcompletion.create', compact('maintenancerequests'));
    }

    public function store(Request $request)
    {
        //dd($request->all());
        $request->validate([
            'Property' => 'required|string|max:50',
            'Block' => 'required|string|max:50',
            'Floor' => 'required|string|max:50',
            'Unit' => 'required|string|max:50',
            'IssueDescription' => 'required|string|max:255',
            'CompletionDate' => 'required|date',
            'WorkDoneSummary' => 'required|string|max:255',
            'PartsUsed' => 'nullable|string|max:255',
            'Cost' => 'required|integer',
            'FinalStatus' => 'required|string|max:50',
        ]);
        //dd('validation passed');
        $workCompletion = PropertyMaintenanceWorkCompletion::create([
            'Property' => $request->Property,
            'Block' => $request->Block,
            'Floor' => $request->Floor,
            'Unit' => $request->Unit,
            'IssueDescription' => $request->IssueDescription,
            'CompletionDate' => $request->CompletionDate,
            'WorkDoneSummary' => $request->WorkDoneSummary,
            'PartsUsed' => $request->PartsUsed,
            'Cost' => $request->Cost,
            'FinalStatus' => $request->FinalStatus,
            'CreatedBy' => auth()->user()->Id,
            'ModifiedBy' => auth()->user()->Id,
        ]);
        //dd('validation passed');
        return redirect()->route('workcompletion.index')->with('success', 'Work completion created successfully');

    }

}
