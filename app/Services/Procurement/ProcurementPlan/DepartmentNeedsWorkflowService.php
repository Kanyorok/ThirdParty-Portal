<?php

namespace App\Services\Procurement\ProcurementPlan;
use App\Enums\WorkflowStatus;
use App\Models\Auth\User;
use App\Models\Procurement\DepartmentNeed;
use App\Models\Core\Approval\Workflow;
use App\Models\Core\Approval\WorkflowStage;
use App\Models\Core\Approval\WorkflowPending;
use App\Models\Core\Approval\WorkflowEscalation;
use App\Models\Core\Approval\WorkflowHistory;
use App\Models\Core\Approval\ApprovalGroups;
use App\Models\Core\Approval\CodeDetail;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DepartmentNeedsWorkflowService extends ApprovalWorkflowService
{
    protected string $codeId = 'DN';

    /**
     * Ensure workflow stages have proper PermissionId set
     */
    public function ensureWorkflowConfiguration(): bool
    {
        try {
            $workflow = Workflow::where('Source', 't_DepartmentNeeds')
                ->whereNull('DeletedOn')
                ->first();

            if (!$workflow) {
                Log::warning('No workflow found for t_DepartmentNeeds');
                return false;
            }

            $approvalGroup = ApprovalGroups::where('DocType', 'purchase_requisition')
                ->whereNull('DeletedOn')
                ->first();

            if (!$approvalGroup || !$approvalGroup->Permission) {
                Log::error('No approval group found with valid permission');
                return false;
            }

            $updated = WorkflowStage::where('WorkFlowId', $workflow->Id)
                ->whereNull('PermissionId')
                ->whereNull('DeletedOn')
                ->update([
                    'PermissionId' => $approvalGroup->Permission,
                    'ModifiedBy' => Auth::id() ?? 1,
                    'ModifiedOn' => now()
                ]);

            if ($updated > 0) {
                Log::info("Updated {$updated} workflow stages with PermissionId: {$approvalGroup->Permission}");
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error ensuring workflow configuration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Validate that workflow stages have proper configuration
     */
    protected function validateWorkflowConfiguration(string $source): bool
    {
        try {
            $workflow = Workflow::where('Source', $source)
                ->whereNull('DeletedOn')
                ->first();

            if (!$workflow) {
                Log::error("No workflow found for source: {$source}");
                return false;
            }

            $missing = WorkflowStage::where('WorkFlowId', $workflow->Id)
                ->whereNull('PermissionId')
                ->whereNull('DeletedOn')
                ->count();

            if ($missing > 0) {
                Log::error("Found {$missing} workflow stages without PermissionId for {$source}");
                $this->ensureWorkflowConfiguration();

                $stillMissing = WorkflowStage::where('WorkFlowId', $workflow->Id)
                    ->whereNull('PermissionId')
                    ->whereNull('DeletedOn')
                    ->count();

                return $stillMissing === 0;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('Error validating workflow configuration: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Submit a department need for approval
     */
    public function submit(DepartmentNeed $departmentNeed, User $actor, string $remarks): bool
    {
        try {
            $source = $departmentNeed->getTable();
            $sourceID = $departmentNeed->getKey();
            $statusID = $this->getStatusID(WorkflowStatus::Submitted->value);

            if (!$this->validateWorkflowConfiguration($source)) {
                throw new \Exception('Workflow is not properly configured.');
            }

            DB::statement('EXEC p_ProcessWorkflowAction @Source = ?, @SourceID = ?, @UserID = ?, @UserName = ?, @StatusID = ?, @Notes = ?',
                [$source, $sourceID, $actor->Id, $actor->Name, $statusID, $remarks]
            );

            DB::statement('EXEC p_ProcessWorkflowPending');

            return true;

        } catch (\Exception $e) {
            Log::error('Error in submit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Approve a department need
     */
    public function approve(DepartmentNeed $departmentNeed, User $actor, string $remarks): bool
    {
        try {
            $source = $departmentNeed->getTable();
            $sourceID = $departmentNeed->getKey();
            $statusID = $this->getStatusID(WorkflowStatus::APPROVED->value);

            if (!$this->validateWorkflowConfiguration($source)) {
                throw new \Exception('Workflow is not properly configured.');
            }

            DB::statement('EXEC p_ProcessWorkflowAction @Source = ?, @SourceID = ?, @UserID = ?, @UserName = ?, @StatusID = ?, @Notes = ?',
                [$source, $sourceID, $actor->Id, $actor->Name, $statusID, $remarks]
            );

            DB::statement('EXEC p_ProcessWorkflowStages');

            return true;

        } catch (\Exception $e) {
            Log::error('Error in approve: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Reject a department need
     */
    public function reject(DepartmentNeed $departmentNeed, User $actor, string $remarks): bool
    {
        try {
            $source = $departmentNeed->getTable();
            $sourceID = $departmentNeed->getKey();
            $statusID = $this->getStatusID(WorkflowStatus::REJECTED->value);

            DB::statement('EXEC p_ProcessWorkflowAction @Source = ?, @SourceID = ?, @UserID = ?, @UserName = ?, @StatusID = ?, @Notes = ?',
                [$source, $sourceID, $actor->Id, $actor->Name, $statusID, $remarks]
            );

            return true;

        } catch (\Exception $e) {
            Log::error('Error in reject: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Escalate overdue workflow items
     */
    public function escalateOverdue(int $runBy = 0): bool
    {
        try {
            DB::statement('EXEC p_EscalateOverdueWorkflows @RunBy = ?', [$runBy]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error in escalation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get workflow history for a department need
     */
    public function getHistory(DepartmentNeed $departmentNeed, int $limit = 1000)
    {
        return WorkflowHistory::with(['creator', 'stage', 'status'])
            ->where('Source', $departmentNeed->getTable())
            ->where('SourceID', $departmentNeed->getKey())
            ->latest('CreatedOn')
            ->limit($limit)
            ->get();
    }

    /**
     * Check if user can approve
     */
    public function canUserApprove(DepartmentNeed $departmentNeed, User $user): bool
    {
        return WorkflowPending::where('Source', $departmentNeed->getTable())
            ->where('SourceID', $departmentNeed->getKey())
            ->where('UserId', $user->Id)
            ->whereNull('DeletedOn')
            ->exists();
    }

    /**
     * Get pending approvals
     */
    public function getPendingApprovals(DepartmentNeed $departmentNeed): array
    {
        return WorkflowPending::with(['stage', 'user'])
            ->where('Source', $departmentNeed->getTable())
            ->where('SourceID', $departmentNeed->getKey())
            ->whereNull('DeletedOn')
            ->orderByRelation('stage.Order', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get escalations
     */
    public function getEscalations(DepartmentNeed $departmentNeed): array
    {
        return WorkflowEscalation::with(['user', 'supervisor', 'stage'])
            ->whereNull('DeletedOn')
            ->orderBy('CreatedOn', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Helper to get status ID from CodeDetails
     */
    protected function getStatusID(string $statusCode): ?int
    {
        return CodeDetail::where('Value', $statusCode)->value('ID');
    }
}
