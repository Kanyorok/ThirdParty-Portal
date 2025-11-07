<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Settings\WorkFlowRequest;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Auth;
use App\Models\Settings\WorkFlow;
use App\Models\Settings\WorkFlowStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class WorkFlowController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $morphMap = Relation::morphMap();

        $workFlowGroups = WorkFlow::all();

        $sourceOptions = array_flip($morphMap);

        return view('settings.approvals.sections', compact('workFlowGroups', 'sourceOptions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WorkFlowRequest $request)
    {
        $validated = $request->validated();
        //dd($request->all());
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();

        try {
            $workFlow = WorkFlow::create([
                'Name' => $validated['Name'],
                'Description' => $validated['Description'],
                'Source' => $validated['DocType'],
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
                'CreatedOn' => now(),
            ]);

            activity()->causedBy($user)
                ->performedOn($workFlow)
                ->event('create')
                ->log('Created approval workflow stage: ' . $validated['Name']);

                        // Log success
        Log::info('Workflow created successfully.', [
            'workflow_id' => $workFlow->id,
            'created_by' => Auth::id(),
        ]);
        } catch (\Exception $e) {

                    // Log the error
        Log::error('Failed to create workflow.', [
            'error_message' => $e->getMessage(),
            'user_id' => Auth::id(),
        ]);
            return redirect()->back()->withErrors(['error' => 'Failed to create approval stage: ' . $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Approval workflow created.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $approval = WorkFlow::findOrFail($id);
        $sourceOptions = array_flip(Relation::morphMap());
        $approvalTypes = DB::table('t_WorkFlowTypes')->get();
        $permissions = DB::table('t_Permissions')->get();
        $workflowLimits = DB::table('t_WorkflowLimits')
            ->select('Id', 'Source')
            ->get();

        $stages = WorkFlowStage::where('WorkFlowId', $id)
            ->with(['workflow'])
            ->get();

        return view('settings.approvals.show', compact('approval', 'sourceOptions', 'permissions', 'approvalTypes', 'workflowLimits', 'stages'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $workFlow = WorkFlow::findOrFail($id);
        $workFlow->delete();
    }
}
