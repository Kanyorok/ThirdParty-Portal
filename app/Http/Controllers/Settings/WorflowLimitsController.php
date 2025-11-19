<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            
            // Get existing limits - NOT grouped, just get all
            $limits = WorkFlowLimit::with(['workflow_stage.workflow', 'permission'])
                ->whereNull('DeletedOn')
                ->orderBy('WorkFlowStageId')
                ->orderBy('MaxAmount', 'asc')
                ->get();

            //  Get existing workflow stage IDs that have limits (for the blade check)
            $existingWorkflowStageIds = WorkFlowLimit::whereNull('DeletedOn')
                ->pluck('WorkFlowStageId')
                ->unique()
                ->toArray();

            // Get count of limits per stage
            $limitsCountPerStage = WorkFlowLimit::whereNull('DeletedOn')
                ->select('WorkFlowStageId', DB::raw('COUNT(*) as limit_count'))
                ->groupBy('WorkFlowStageId')
                ->pluck('limit_count', 'WorkFlowStageId')
                ->toArray();
            
            Log::info('Data fetched successfully for WorkFlow Limits Index.', [
                'stages_count' => $workflowStages->count(),
                'total_limits' => $limits->count(),
                'stages_with_limits' => count($existingWorkflowStageIds)
            ]);

            // Pass the existingWorkflowStageIds to the view
            return view('settings.approvals.workflowlimitsetup', 
                compact('workflowStages', 'permissions', 'limits', 'limitsCountPerStage', 'existingWorkflowStageIds'));

        } catch (\Exception $e) {
            Log::error('Error fetching data for WorkFlow Limits Index.', [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
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
            'stage_id' => $validated['WorkFlowStageId'],
            'amount' => $validated['AmountLimit']
        ]);

        try {
            // --- CHECK 1: Duplicate Amount for Same Stage ---
            $existingLimit = WorkFlowLimit::where('WorkFlowStageId', $validated['WorkFlowStageId'])
                ->where('MaxAmount', $validated['AmountLimit'])
                ->whereNull('DeletedOn')
                ->first();

            if ($existingLimit) {
                Log::warning('WorkFlow Limit with this amount already exists for stage.', [
                    'stage_id' => $validated['WorkFlowStageId'],
                    'amount' => $validated['AmountLimit']
                ]);
                return redirect()->back()->withErrors([
                    'error' => 'A workflow limit with amount ' . number_format($validated['AmountLimit'], 2) . ' already exists for this stage. Please use a different amount.'
                ]);
            }

            // --- CHECK 2: Validate Workflow Stage ---
            $workflowStage = WorkFlowStage::with(['workflow', 'type_name'])
                ->where('Id', $validated['WorkFlowStageId'])
                ->whereHas('type_name', function($query) {
                    $query->where('TypeID', 'AMT');
                })
                ->first();

            if (!$workflowStage) {
                Log::error('Invalid or non-AMT workflow stage selected.', [
                    'stage_id' => $validated['WorkFlowStageId']
                ]);
                return redirect()->back()->withErrors([
                    'error' => 'Invalid workflow stage or stage is not AMT type.'
                ]);
            }
            
            // --- PREPARE DATA FOR SP CALL ---
            // Get existing limits count for this stage to create unique permission name
            $existingCount = WorkFlowLimit::where('WorkFlowStageId', $validated['WorkFlowStageId'])
                ->whereNull('DeletedOn')
                ->count();
            
            // Generate unique permission name for workflow limit with tier number
            $tierNumber = $existingCount + 1;
            $permissionName = sprintf(
                'workflow_limit_%s_tier%d_upto_%s',
                str_replace(' ', '_', strtolower($workflowStage->StageName)),
                $tierNumber,
                number_format($validated['AmountLimit'], 0, '', '')
            );
            
            $moduleID = 98006200; // Default module ID
            
            Log::info('Prepared data for Stored Procedure call.', [
                'stage_id' => $validated['WorkFlowStageId'],
                'stage_name' => $workflowStage->StageName,
                'max_amount' => $validated['AmountLimit'],
                'permission_name' => $permissionName,
                'module_id' => $moduleID,
                'created_by' => $userId,
                'tier_number' => $tierNumber
            ]);

            // --- SP CALL ---
            $params = [
                $validated['WorkFlowStageId'],
                $validated['AmountLimit'],
                $permissionName,
                $moduleID,
                $userId
            ];

            DB::beginTransaction();

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
                DB::rollBack();
                Log::critical('SQL Stored Procedure execution failed.', [
                    'sp_name' => 'p_CreateWorkflowLimitWithPermission',
                    'params' => $params,
                    'db_error' => $db_e->getMessage()
                ]);
                return redirect()->back()->withErrors([
                    'error' => 'Database error during limit creation: ' . $db_e->getMessage()
                ]);
            }

            // --- SP RESULT PARSING ---
            if (!empty($result) && isset($result[0]->Message)) {
                $message = $result[0]->Message;
                
                if (strpos($message, 'Error:') !== false) {
                    DB::rollBack();
                    Log::error('Stored Procedure returned an error message.', ['message' => $message]);
                    return redirect()->back()->withErrors(['error' => $message]);
                }

                // Success case
                $workflowLimitID = $result[0]->WorkflowLimitID ?? null;
                $permissionID = $result[0]->PermissionID ?? null;

                if ($workflowLimitID) {
                    DB::commit();
                    
                    Log::info('WorkFlow Limit created successfully via SP.', [
                        'WorkflowLimitID' => $workflowLimitID,
                        'PermissionID' => $permissionID,
                        'PermissionName' => $permissionName,
                        'TierNumber' => $tierNumber,
                        'Amount' => $validated['AmountLimit']
                    ]);

                    // Log activity
                    $workflowLimit = WorkFlowLimit::find($workflowLimitID);
                    if ($workflowLimit) {
                        activity()->causedBy($user)
                            ->performedOn($workflowLimit)
                            ->event('create')
                            ->log(sprintf(
                                'Created workflow limit tier %d for stage: %s with amount: %s (Permission: %s)',
                                $tierNumber,
                                $workflowStage->StageName,
                                number_format($validated['AmountLimit'], 2),
                                $permissionName
                            ));
                    }

                    return redirect()->back()->with('success', sprintf(
                        'Workflow limit tier %d created successfully! Amount: %s | Permission: %s - Assign users to this permission to allow them to approve amounts up to this limit.',
                        $tierNumber,
                        number_format($validated['AmountLimit'], 2),
                        $permissionName
                    ));
                }
            }

            DB::rollBack();
            Log::error('Failed to create workflow limit. Unexpected response from stored procedure.', [
                'result' => $result
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Failed to create workflow limit. No valid ID returned from stored procedure.'
            ]);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            Log::critical('General exception during WorkFlow Limit store process.', [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Critical error creating workflow limit: ' . $e->getMessage()
            ]);
        }
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
            $amount = $limit->MaxAmount;
            $permissionName = $limit->permission->name ?? 'Unknown';
            
            DB::beginTransaction();
            
            // Soft delete the limit
            $limit->update([
                'DeletedBy' => $userId,
                'DeletedOn' => now(),
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);
            
            Log::info('WorkFlow Limit soft deleted successfully.', [
                'limit_id' => $Id,
                'deleted_by' => $userId,
                'stage_name' => $stageName,
                'amount' => $amount,
                'permission' => $permissionName
            ]);
            
            // Log activity after deletion
            activity()->causedBy($user)
                ->performedOn($limit)
                ->event('delete')
                ->log(sprintf(
                    'Deleted workflow limit for stage: %s (Amount: %s, Permission: %s)',
                    $stageName,
                    number_format($amount, 2),
                    $permissionName
                ));

            DB::commit();

            return redirect()->back()->with('success', sprintf(
                'Workflow limit deleted successfully. Users with permission "%s" will no longer be able to approve at this amount level.',
                $permissionName
            ));
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $mnfe) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            Log::warning('WorkFlow Limit not found for deletion.', ['limit_id' => $Id]);
            return redirect()->back()->withErrors([
                'error' => 'The specified workflow limit was not found.'
            ]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            Log::critical('Error deleting WorkFlow Limit.', [
                'limit_id' => $Id,
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->withErrors([
                'error' => 'Failed to delete limit: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get limits for a specific stage (AJAX endpoint)
     */
    public function getLimitsForStage($stageId)
    {
        try {
            $limits = WorkFlowLimit::with(['permission'])
                ->where('WorkFlowStageId', $stageId)
                ->whereNull('DeletedOn')
                ->orderBy('MaxAmount', 'asc')
                ->get()
                ->map(function($limit) {
                    return [
                        'Id' => $limit->Id,
                        'MaxAmount' => $limit->MaxAmount,
                        'PermissionId' => $limit->PermissionId,
                        'PermissionName' => $limit->permission->name ?? 'N/A',
                    ];
                });

            Log::info('Fetched limits for stage via AJAX', [
                'stage_id' => $stageId,
                'limits_count' => $limits->count()
            ]);

            return response()->json([
                'success' => true,
                'limits' => $limits
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching limits for stage', [
                'stage_id' => $stageId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch limits'
            ], 500);
        }
    }
}