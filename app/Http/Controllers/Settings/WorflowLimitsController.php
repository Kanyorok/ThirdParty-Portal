<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\WorkFlowLimitRequest;
use App\Models\Core\Approval\Permission;
use App\Models\Core\Approval\WorkflowStage;
use App\Models\Settings\WorkFlowLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $workflowStages = WorkflowStage::with(['workflow', 'type_name'])
                ->whereHas('type_name', function ($query) {
                    $query->where('TypeID', 'AMT');
                })
                ->whereNull('DeletedOn')
                ->get()
                ->map(function ($stage) {
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
                'stages_with_limits' => count($existingWorkflowStageIds),
            ]);

            // Pass the existingWorkflowStageIds to the view
            return response()->view(
                'settings.approvals.workflowlimitsetup',
                compact('workflowStages', 'permissions', 'limits', 'limitsCountPerStage', 'existingWorkflowStageIds')
            )
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            Log::error('Error fetching data for WorkFlow Limits Index.', [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
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

        $stageId = $validated['WorkFlowStageId'];
        $amounts = $validated['AmountLimit'] ?? []; // Expect array

        if (! is_array($amounts) || empty($amounts)) {
            return redirect()->back()->withErrors(['error' => 'At least one amount limit is required.']);
        }

        // Sort amounts ascending for logical tier assignment (lower amount = lower tier)
        sort($amounts, SORT_NUMERIC);

        Log::info('Attempting to create multiple WorkFlow Limits.', [
            'user_id' => $userId,
            'stage_id' => $stageId,
            'amounts' => $amounts,
        ]);

        $workflowStage = WorkflowStage::with(['workflow', 'type_name'])
            ->where('Id', $stageId)
            ->whereHas('type_name', function ($query) {
                $query->where('TypeID', 'AMT');
            })
            ->first();

        if (! $workflowStage) {
            Log::error('Invalid or non-AMT workflow stage selected.', ['stage_id' => $stageId]);

            return redirect()->back()->withErrors(['error' => 'Invalid workflow stage or stage is not AMT type.']);
        }

        $existingLimits = WorkFlowLimit::where('WorkFlowStageId', $stageId)
            ->whereNull('DeletedOn')
            ->orderBy('MaxAmount', 'asc')
            ->get();

        $existingAmounts = $existingLimits->pluck('MaxAmount')->toArray();
        $existingCount = count($existingAmounts);

        $newAmounts = array_unique($amounts); // Remove duplicates in new
        if (count($newAmounts) < count($amounts)) {
            return redirect()->back()->withErrors(['error' => 'Duplicate amounts provided in the new limits.']);
        }

        $duplicateErrors = [];
        foreach ($newAmounts as $amount) {
            if (in_array($amount, $existingAmounts)) {
                $duplicateErrors[] = 'Amount ' . number_format($amount, 2) . ' already exists for this stage.';
            }
        }

        if (! empty($duplicateErrors)) {
            return redirect()->back()->withErrors(['error' => implode(' ', $duplicateErrors)]);
        }

        DB::beginTransaction();

        try {
            $createdLimits = [];
            $moduleID = 98006200; // Default module ID
            $stageName = str_replace(' ', '_', strtolower($workflowStage->StageName));

            foreach ($newAmounts as $index => $amount) {
                // Calculate tier for this new amount
                $tierNumber = $existingCount + $index + 1;

                // Generate unique permission name
                $permissionName = sprintf(
                    'workflow_limit_%s_tier%d_upto_%s',
                    $stageName,
                    $tierNumber,
                    number_format($amount, 0, '', '')
                );

                Log::info('Prepared data for Stored Procedure call.', [
                    'stage_id' => $stageId,
                    'stage_name' => $workflowStage->StageName,
                    'max_amount' => $amount,
                    'permission_name' => $permissionName,
                    'module_id' => $moduleID,
                    'created_by' => $userId,
                    'tier_number' => $tierNumber,
                ]);

                $params = [
                    $stageId,
                    $amount,
                    $permissionName,
                    $moduleID,
                    $userId,
                ];

                $result = DB::select(
                    'EXEC p_CreateWorkflowLimitWithPermission 
                    @WorkFlowStageId = ?,
                    @MaxAmount = ?,
                    @PermissionName = ?,
                    @ModuleID = ?,
                    @CreatedBy = ?',
                    $params
                );

                Log::debug('Stored Procedure Result Received.', ['result' => $result]);

                if (! empty($result) && isset($result[0]->Message)) {
                    $message = $result[0]->Message;

                    if (strpos($message, 'Error:') !== false) {
                        throw new \Exception($message);
                    }

                    $workflowLimitID = $result[0]->WorkflowLimitID ?? null;
                    $permissionID = $result[0]->PermissionID ?? null;

                    if (! $workflowLimitID) {
                        throw new \Exception('No valid ID returned from stored procedure for amount ' . $amount);
                    }

                    $createdLimits[] = [
                        'id' => $workflowLimitID,
                        'tier' => $tierNumber,
                        'amount' => $amount,
                        'permission' => $permissionName,
                    ];

                    // Log activity for each
                    $workflowLimit = WorkFlowLimit::find($workflowLimitID);
                    if ($workflowLimit) {
                        activity()->causedBy($user)
                            ->performedOn($workflowLimit)
                            ->event('create')
                            ->log(sprintf(
                                'Created workflow limit tier %d for stage: %s with amount: %s (Permission: %s)',
                                $tierNumber,
                                $workflowStage->StageName,
                                number_format($amount, 2),
                                $permissionName
                            ));
                    }
                } else {
                    throw new \Exception('Unexpected response from stored procedure for amount ' . $amount);
                }
            }

            DB::commit();

            Log::info('Multiple WorkFlow Limits created successfully.', [
                'stage_id' => $stageId,
                'created_count' => count($createdLimits),
            ]);

            // Prepare success message with details
            $successMessages = array_map(function ($limit) {
                return sprintf('Tier %d: Amount %s | Permission: %s', $limit['tier'], number_format($limit['amount'], 2), $limit['permission']);
            }, $createdLimits);

            return redirect()->back()->with('success', 'Workflow limits created successfully! ' . implode('; ', $successMessages) . ' - Assign users to these permissions accordingly.');
        } catch (\Exception $e) {
            DB::rollBack();

            Log::critical('Exception during multiple WorkFlow Limit creation.', [
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $userId,
            ]);

            return redirect()->back()->withErrors(['error' => 'Error creating workflow limits: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $Id, Request $request)
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
                'permission' => $permissionName,
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

            $successMessage = sprintf(
                'Workflow limit deleted successfully. Users with permission "%s" will no longer be able to approve at this amount level.',
                $permissionName
            );

            // Check if request is AJAX
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $successMessage,
                ]);
            }

            return redirect()->back()->with('success', $successMessage);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $mnfe) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::warning('WorkFlow Limit not found for deletion.', ['limit_id' => $Id]);

            $errorMessage = 'The specified workflow limit was not found.';

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 404);
            }

            return redirect()->back()->withErrors(['error' => $errorMessage]);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            Log::critical('Error deleting WorkFlow Limit.', [
                'limit_id' => $Id,
                'error_message' => $e->getMessage(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            $errorMessage = 'Failed to delete limit: ' . $e->getMessage();

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $errorMessage,
                ], 500);
            }

            return redirect()->back()->withErrors(['error' => $errorMessage]);
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
                ->map(function ($limit) {
                    return [
                        'Id' => $limit->Id,
                        'MaxAmount' => $limit->MaxAmount,
                        'PermissionId' => $limit->PermissionId,
                        'PermissionName' => $limit->permission->name ?? 'N/A',
                    ];
                });

            Log::info('Fetched limits for stage via AJAX', [
                'stage_id' => $stageId,
                'limits_count' => $limits->count(),
            ]);

            return response()->json([
                'success' => true,
                'limits' => $limits,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching limits for stage', [
                'stage_id' => $stageId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch limits',
            ], 500);
        }
    }
}
