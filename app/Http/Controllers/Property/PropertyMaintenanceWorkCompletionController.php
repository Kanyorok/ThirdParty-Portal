<?php

namespace App\Http\Controllers\Property;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\MaintenanceAndIssues\PropertyMaintenanceWorkCompletionRequest;
use App\Services\Property\MaintenanceAndIssues\PropertyMaintenanceWorkCompletionService;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;

class PropertyMaintenanceWorkCompletionController extends Controller
{
    //
    public function index()
    {
        $workCompletions = PropertyMaintenanceWorkCompletion::with('request','finalstatus')->get();
        return view('property.maintenanceandissues.workcompletion.index', compact('workCompletions'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionCreate, PropertyMaintenanceWorkCompletion::class);
        $assignments = PropertyMaintenanceAssign::with('request')->get();
        $finalstatus = CodeDetail::where('CodeID', 'FinalStatus')->get();
        return view('property.maintenanceandissues.workcompletion.create', compact('assignments', 'finalstatus'));
    }

    public function store(PropertyMaintenanceWorkCompletionRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionCreate, PropertyMaintenanceWorkCompletion::class);

        $validated = $request->validated();

        $requestNumber = PropertyMaintenanceAssign::findOrFail($validated['RequestNumber']);
        $finalstatus = CodeDetail::findOrFail((int) $validated['FinalStatus']);$finalstatus = CodeDetail::findOrFail((int) $validated['FinalStatus']);
        $document = $request->file('Document');

        $user = Auth::user();
        //dd('validation passed');
        $workCompletion = PropertyMaintenanceWorkCompletionService::create(
            $requestNumber,
            $validated['CompletionDate'],
            $validated['WorkDoneSummary'],
            $validated['PartsUsed'],
            $validated['Cost'],
            $finalstatus,
            auth()->user(),
            $document
        );
        
        return redirect()->route('workcompletion.index')->with('success', 'Work completion created successfully');

    }
    public function edit($Id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignUpdate, PropertyMaintenanceAssign::class);
        $workCompletion = PropertyMaintenanceWorkCompletion::findOrFail($Id);
        $assignments = PropertyMaintenanceAssign::with('request')->get();
        $finalstatus = CodeDetail::where('CodeID', 'FinalStatus')->get();
        return view('property.maintenanceandissues.workcompletion.edit', compact('assignments', 'finalstatus', 'workCompletion'));
    }

    public function update(PropertyMaintenanceWorkCompletionRequest $request, $Id)
    {
      $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionUpdate, PropertyMaintenanceWorkCompletion::class);
        $validated = $request->validated();
        
            $workCompletions = PropertyMaintenanceWorkCompletion::findOrFail($Id);

            $document = $request->file('Document');
            $finalstatus = CodeDetail::findOrFail((int) $validated['FinalStatus']);

            $Completions = PropertyMaintenanceWorkCompletionService::update(
                $workCompletions,
                $validated['CompletionDate'],
                $validated['WorkDoneSummary'],
                $validated['PartsUsed'],
                $validated['Cost'],
                $finalstatus,
                auth()->user(),
                $document
            );

            return redirect()->route('workcompletion.index')->with('success', 'Work completion updated successfully');
    }
    public function show($Id)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionView, PropertyMaintenanceWorkCompletion::class);
        $workCompletion = PropertyMaintenanceWorkCompletion::with('request', 'finalstatus')->findOrFail($Id);
        return view('property.maintenanceandissues.workcompletion.show', compact('workCompletion'));
    }


    public function destroy($Id)
    {
        //Check if user has permission to delete property categories
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionDelete, PropertyMaintenanceWorkCompletion::class);
        try {
            $workCompletion = PropertyMaintenanceWorkCompletion::findOrFail($Id);
            $workCompletion->delete();

            return redirect()->route('workcompletion.index')
                ->with('success', 'Property Work Completion Deleted Successfully!');
        } catch (\Throwable $th) {
            // Log the error for debugging
            Log::error('Error deleting property work completion: ' . $th->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Failed to delete Property Maintenance Work Completion. Please try again.'])
                ->withInput();
        }
    }

}
