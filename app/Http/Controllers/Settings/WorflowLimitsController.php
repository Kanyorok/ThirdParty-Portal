<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // <-- ADDED: Laravel Logging Facade
use Illuminate\Database\Eloquent\Relations\Relation;
use App\Http\Requests\Settings\WorkFlowLimitRequest;
use App\Models\Core\Approval\WorkFlowStage;
use Illuminate\Support\Facades\Auth;
use App\Models\Core\Approval\Permission;

use App\Models\Settings\WorkFlowLimit;

class WorflowLimitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Log::info('Accessing WorkFlow Limits Index Page.');
        try {
            // Get workflow stages with AMT type
            $workflowStages = WorkFlowStage::with(['workflow', 'type_name'])
                ->whereHas('type_name', function($query) {
                    $query->where('TypeID', 'AMT');
                })
                ->whereNull('DeletedOn')
                ->get()
                ->map(function($stage) {
                    $stage->workflow_name = $stage->workflow->Name ?? 'N/A';
                    $stage->workflow_source = $stage->workflow->Source ?? 'N/A';
                    return $stage;
                });

            $permissions = DB::table('t_Permissions')->get();
            
            // Get existing limits with relationships
            $limits = WorkFlowLimit::with(['workflow_stage.workflow', 'permission'])
                ->whereNull('DeletedOn')
                ->get();

            // Get existing workflow stage IDs that already have limits
            $existingWorkflowStageIds = WorkFlowLimit::whereNull('DeletedOn')
                ->pluck('WorkFlowStageId')
                ->toArray();
            
            // Log successful data retrieval
            Log::info('Data fetched successfully for WorkFlow Limits Index.', [
                'stages_count' => $workflowStages->count(),
                'limits_count' => $limits->count()
            ]);

            return view('settings.approvals.workflowlimitsetup', 
                compact('workflowStages', 'permissions', 'limits', 'existingWorkflowStageIds'));

        } catch (\Exception $e) {
            // Log any errors during data retrieval
            Log::error('Error fetching data for WorkFlow Limits Index.', [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            // Return to a generic error view or back with a message
            return redirect()->back()->withErrors(['error' => 'Failed to load workflow limit setup data.']);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(WorkFlowLimitRequest $request)
    {
        $validated = $request->validated();
        /** @var \App\Models\Auth\User $user */
        $user = Auth::user();
        $userId = $user->Id ?? 'UNKNOWN';

        Log::info('Attempting to create new WorkFlow Limit.', [
            'user_id' => $userId,
            'validated_data' => $validated
        ]);

        try {
            // --- CHECK 1: Existing Limit ---
            $existingLimit = WorkFlowLimit::where('WorkFlowStageId', $validated['WorkFlowStageId'])
                ->whereNull('DeletedOn')
                ->first();

            if ($existingLimit) {
                Log::warning('WorkFlow Limit already exists for stage.', ['stage_id' => $validated['WorkFlowStageId']]);
                return redirect()->back()->withErrors(['error' => 'This workflow stage already has a limit configured.']);
            }

            // --- CHECK 2: Validate Workflow Stage ---
            $workflowStage = WorkFlowStage::with(['workflow', 'type_name'])
                ->where('Id', $validated['WorkFlowStageId'])
                ->whereHas('type_name', function($query) {
                    $query->where('TypeID', 'AMT');
                })
                ->first();

            if (!$workflowStage) {
                Log::error('Invalid or non-AMT workflow stage selected.', ['stage_id' => $validated['WorkFlowStageId']]);
                return redirect()->back()->withErrors(['error' => 'Invalid workflow stage or stage is not AMT type.']);
            }
            
            // --- CHECK 3: Validate Permission ---
            $permission = Permission::find($validated['Permission']);
            if (!$permission) {
                Log::error('Invalid permission ID provided.', ['permission_id' => $validated['Permission']]);
                return redirect()->back()->withErrors(['error' => 'Invalid permission selected.']);
            }
            
            // --- PREPARE DATA FOR SP CALL ---
            // Generate unique permission name for workflow limit
            $permissionName = 'workflow_limit_' . $workflowStage->StageName . '_' . time();
            $moduleID = 98006200; // Default module ID, adjust based on your application
            
            Log::info('Prepared data for Stored Procedure call.', [
                'stage_id' => $validated['WorkFlowStageId'],
                'max_amount' => $validated['AmountLimit'],
                'permission_name' => $permissionName,
                'module_id' => $moduleID,
                'created_by' => $userId
            ]);

            // --- SP CALL ---
            $params = [
                $validated['WorkFlowStageId'],
                $validated['AmountLimit'],
                $permissionName,
                $moduleID,
                $userId
            ];

            try {
                $result = DB::select('EXEC p_CreateWorkflowLimitWithPermission 
                    @WorkFlowStageId = ?,
                    @MaxAmount = ?,
                    @PermissionName = ?,
                    @ModuleID = ?,
                    @CreatedBy = ?', 
                    $params
                );
                
                Log::debug('Stored Procedure Result Received.', ['result' => $result]);

            } catch (\Exception $db_e) {
                // Log the exact database error during SP execution
                Log::critical('SQL Stored Procedure execution failed.', [
                    'sp_name' => 'p_CreateWorkflowLimitWithPermission',
                    'params' => $params,
                    'db_error' => $db_e->getMessage()
                ]);
                return redirect()->back()->withErrors(['error' => 'Database error during limit creation: ' . $db_e->getMessage()]);
            }

            // --- SP RESULT PARSING ---
            if (!empty($result) && isset($result[0]->Message)) {
                $message = $result[0]->Message;
                
                if (strpos($message, 'Error:') !== false) {
                    Log::error('Stored Procedure returned an error message.', ['message' => $message]);
                    return redirect()->back()->withErrors(['error' => $message]);
                }

                // Success case
                $workflowLimitID = $result[0]->WorkflowLimitID ?? null;
                $permissionID = $result[0]->PermissionID ?? null;

                if ($workflowLimitID) {
                    Log::info('WorkFlow Limit created successfully via SP.', [
                        'WorkflowLimitID' => $workflowLimitID,
                        'PermissionID' => $permissionID
                    ]);

                    // Log activity
                    $workflowLimit = WorkFlowLimit::find($workflowLimitID);
                    if ($workflowLimit) {
                        activity()->causedBy($user)
                            ->performedOn($workflowLimit)
                            ->event('create')
                            ->log('Created workflow limit for stage: ' . $workflowStage->StageName . ' with amount: ' . $validated['AmountLimit']);
                    } else {
                        Log::warning('Could not find newly created WorkFlowLimit for activity logging.', ['id' => $workflowLimitID]);
                    }

                    return redirect()->back()->with('success', 'Workflow limit and permission created successfully.');
                }
            }

            // Fallback for unexpected SP result structure
            Log::error('Failed to create workflow limit. Unexpected or empty response from stored procedure.', ['result' => $result]);
            return redirect()->back()->withErrors(['error' => 'Failed to create workflow limit. No valid ID returned from stored procedure.']);

        } catch (\Exception $e) {
            // Catch any unexpected PHP or application errors
            Log::critical('General exception during WorkFlow Limit store process.', [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'user_id' => $userId
            ]);
            return redirect()->back()->withErrors(['error' => 'Critical error creating workflow limit: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // ... (No changes needed here unless you implement this)
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        // ... (No changes needed here unless you implement this)
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // ... (No changes needed here unless you implement this)
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $Id)
    {
        Log::info('Attempting to delete WorkFlow Limit.', ['limit_id' => $Id]);
        
        try {
            $limit = WorkFlowLimit::findOrFail($Id);
            
            /** @var \App\Models\Auth\User $user */
            $user = Auth::user();
            $userId = $user->Id ?? 'UNKNOWN';
            
            // Get workflow stage for logging
            $workflowStage = $limit->workflow_stage;
            $stageName = $workflowStage ? $workflowStage->StageName : 'Unknown';
            
            // --- SOFT DELETE ---
            $limit->update([
                'DeletedBy' => $userId,
                'DeletedOn' => now(),
            ]);
            
            Log::info('WorkFlow Limit soft deleted successfully.', [
                'limit_id' => $Id,
                'deleted_by' => $userId,
                'stage_name' => $stageName
            ]);
            
            // Log activity after deletion
            activity()->causedBy($user)
                ->performedOn($limit)
                ->event('delete')
                ->log('Soft deleted approval workflow limit for stage: ' . $stageName);

            return redirect()->back()->with('success', 'Workflow limit deleted successfully');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $mnfe) {
             Log::warning('WorkFlow Limit not found for deletion.', ['limit_id' => $Id]);
             return redirect()->back()->withErrors(['error' => 'The specified workflow limit was not found.']);
        } catch (\Exception $e) {
            // Log any errors during the deletion process
            Log::critical('Error deleting WorkFlow Limit.', [
                'limit_id' => $Id,
                'error_message' => $e->getMessage(),
                'line' => $e->getLine()
            ]);
            return redirect()->back()->withErrors(['error' => 'Failed to delete limit: ' . $e->getMessage()]);
        }
    }
}