<?php

namespace App\Http\Controllers\Property;


use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Property\MaintenanceAndIssues\PropertyMaintenanceWorkCompletionRequest;
use App\Services\Property\MaintenanceAndIssues\PropertyMaintenanceWorkCompletionService;
use App\Models\Core\CodeDetail;
use App\Models\PropertyManagement\PropertyMaintenanceAssign;
use Illuminate\Http\Request;
use App\Models\PropertyManagement\PropertyMaintenanceWorkCompletion;
use App\Models\PropertyManagement\PropertyMaintenanceRequest;

class PropertyMaintenanceWorkCompletionController extends Controller
{
    //
    public function index()
    {
        $workCompletions = PropertyMaintenanceWorkCompletion::all();
        return view('property.maintenanceandissues.workcompletion.index', compact('workCompletions'));
    }

    public function create(){
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionCreate, PropertyMaintenanceWorkCompletion::class);
        $assignments = PropertyMaintenanceAssign::all();
        $finalstatus = CodeDetail::where('CodeID', 'FinalStatus')->get();
        return view('property.maintenanceandissues.workcompletion.create', compact('assignments', 'finalstatus'));
    }

    public function store(PropertyMaintenanceWorkCompletionRequest $request)
    {
        $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionCreate, PropertyMaintenanceWorkCompletion::class);
        //dd($request->all());
        $validated = $request->validated();

        $requestNumber = PropertyMaintenanceAssign::findOrFail($validated['RequestNumber']);
        $finalstatus = CodeDetail::findOrFail((int) $validated['FinalStatus']);

        $user     = Auth::user();
        //dd('validation passed');
        $workCompletion = PropertyMaintenanceWorkCompletionService::create(
            $requestNumber,
            $validated['Property'],
            $validated['Block'],
            $validated['Floor'],
            $validated['Unit'],
            $validated['CompletionDate'],
            $validated['WorkDoneSummary'],
            $validated['PartsUsed'],
            $validated['Cost'],
            $finalstatus,
            auth()->user(),
        );        return redirect()->route('workcompletion.index')->with('success', 'Work completion created successfully');

    }
    public function edit($Id)
    {
        //Check if user has permission to edit tender categories
        $this->authorize(PermissionEnum::PropertyMaintenanceAssignUpdate, PropertyMaintenanceAssign::class);
        $workCompletion = PropertyMaintenanceWorkCompletion::findOrFail($Id);
        $assignments = PropertyMaintenanceAssign::all();
        $finalstatus = CodeDetail::where('CodeID', 'FinalStatus')->get();
        return view('property.maintenanceandissues.workcompletion.edit', compact('assignments', 'finalstatus', 'workCompletion'));
    }

    public function update(PropertyMaintenanceWorkCompletionRequest $request, $Id)
    {
      $this->authorize(PermissionEnum::PropertyMaintenanceWorkCompletionUpdate, PropertyMaintenanceWorkCompletion::class);
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $workCompletions = PropertyMaintenanceWorkCompletion::findOrFail($Id);

            $workCompletions->update([
                'RequestNumber' => $validated['RequestNumber'],
                'Property' => $validated['Property'],
                'Block' => $validated['Block'],
                'Floor' => $validated['Floor'],
                'Unit' => $validated['Unit'],
                'CompletionDate' => $validated['CompletionDate'],
                'WorkDoneSummary' => $validated['WorkDoneSummary'],
                'PartsUsed' => $validated['PartsUsed'],
                'Cost' => $validated['Cost'],
                'FinalStatus' => $validated['FinalStatus'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            DB::commit();
            activity()
                ->performedOn($workCompletions)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Work completion');

            return redirect()->route('workcompletion.index')->with('success', 'Assignment updated successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Failed to Update property work completion :' . $th->getMessage());

            return back()->withErrors(['error' => 'Failed to update property work completion '])->withInput();
        }
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
