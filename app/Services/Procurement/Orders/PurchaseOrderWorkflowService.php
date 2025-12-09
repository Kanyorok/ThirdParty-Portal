<?php

namespace App\Services\Procurement\Orders;

use App\Enums\Core\ApprovalEnum;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Support\Facades\DB;

class PurchaseOrderWorkflowService extends ApprovalWorkflowService
{
    // We'll use 'ApprovalStatus' or similar code ID if it exists, 
    // but typically we map enum values to the CodeDetail description.
    // For PO, we might use 'PurchaseOrderStatus' or reuse 'RequisitionStatus' if appropriate.
    // Let's assume 'PurchaseOrderStatus' or similar. 
    // However, RFQWorkflowService uses 'RequisitionStatus'.
    // I'll use 'PurchaseOrderStatus' and if it fails I'll check DB.
    // Actually, let's use 'ApprovalStatus' which is generic.
    protected string $codeId = 'ApprovalStatus';

    /**
     * Submit an Order for approval
     */
    public function submit(Order $order, User $actor, string $remarks): bool
    {
        // 'Submitted' status
        $status = self::codeDetail(ApprovalEnum::Submitted, $this->codeId);

        return $this->submittedAction(
            $actor,
            $status,
            $order,
            'OrderID', // Explicitly use 'OrderID'
            $order->getKey(),
            $remarks
        );
    }

    /**
     * Approve an Order
     */
    public function approve(Order $order, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(ApprovalEnum::Approved, $this->codeId);
        return $this->approveAction($actor, $status, 'OrderID', $order->getKey(), $remarks, 'DocStatus');
    }

    /**
     * Reject an Order
     */
    public function reject(Order $order, User $actor, string $remarks): bool
    {
        $status = self::codeDetail(ApprovalEnum::Rejected, $this->codeId);
        return $this->rejectAction($actor, $status, 'OrderID', $order->getKey(), $remarks, 'DocStatus');
    }

    /**
     * Return an Order for modification
     */
    public function return(Order $order, User $actor, string $remarks): bool
    {
        // Set back to 'Pending' for modification
        $status = self::codeDetail(ApprovalEnum::Pending, $this->codeId);
        return $this->approveAction($actor, $status, 'OrderID', $order->getKey(), $remarks, 'DocStatus');
    }

    /**
     * Get workflow history for an Order
     */
    public function getHistory(Order $order, int $limit = 1000)
    {
        return $this->historyData('OrderID', $limit)
            ->where('SourceID', $order->getKey())
            ->values();
    }

    /**
     * Check if user can perform workflow action
     */
    public function canUserApprove(Order $order, User $user): bool
    {
        // Check if user has pending workflow task
        $pending = DB::select("
            SELECT COUNT(*) as count 
            FROM t_WorkFlowPending 
            WHERE Source = ? AND SourceID = ? AND UserId = ? AND DeletedOn IS NULL
        ", ['OrderID', $order->getKey(), $user->Id]);

        return ($pending[0]->count ?? 0) > 0;
    }

    /**
     * Get pending approvals for an Order
     */
    public function getPendingApprovals(Order $order): array
    {
        return DB::select("
            SELECT 
                p.*, 
                ws.StageName as stage_name, 
                u.Name as user_name,
                u.Email as user_email,
                wt.TypeID as workflow_type
            FROM t_WorkFlowPending p
            JOIN t_WorkFlowStages ws ON p.Stage = ws.Id
            JOIN t_Users u ON p.UserId = u.Id
            JOIN t_WorkFlowTypes wt ON ws.WorkFlowTypeId = wt.Id
            WHERE p.Source = ? AND p.SourceID = ? AND p.DeletedOn IS NULL
            ORDER BY ws.[Order] ASC
        ", ['OrderID', $order->getKey()]);
    }

    /**
     * Check if Order is fully approved
     */
    public function isFullyApproved(Order $order): bool
    {
        $pendingCount = DB::select("
            SELECT COUNT(*) as count 
            FROM t_WorkFlowPending 
            WHERE Source = ? AND SourceID = ? AND DeletedOn IS NULL
        ", ['OrderID', $order->getKey()]);

        return ($pendingCount[0]->count ?? 0) === 0;
    }
}
