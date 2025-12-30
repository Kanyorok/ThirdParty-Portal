<?php

namespace App\Services\Procurement\Orders;

use App\Enums\Core\ApprovalEnum;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseOrderWorkflowService extends ApprovalWorkflowService
{
    protected string $codeId = 'ApprovalStatus';

    /**
     * Submit an Order for approval
     */
    public function submit(Order $order, User $actor, string $remarks = 'Submitted'): bool
    {
        try {
            $status = self::codeDetail(ApprovalEnum::Submitted, $this->codeId);

            $result = $this->submittedAction(
                $actor,
                $status,
                $order,
                'OrderID',
                $order->getKey(),
                $remarks
            );

            // Handle both boolean and array returns
            if (is_bool($result)) {
                return $result;
            }

            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            Log::error('Order submission failed in workflow service', [
                'order_id' => $order->getKey(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Approve an Order
     */
    public function approve(Order $order, User $actor, string $remarks = 'Approved'): bool
    {
        try {
            $status = self::codeDetail(ApprovalEnum::Approved, $this->codeId);
            
            $result = $this->approveAction(
                $actor,
                $status,
                'OrderID',
                $order->getKey(),
                $remarks,
                'DocStatus'
            );

            // Handle both boolean and array returns
            if (is_bool($result)) {
                return $result;
            }

            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            Log::error('Order approval failed in workflow service', [
                'order_id' => $order->getKey(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Reject an Order
     */
    public function reject(Order $order, User $actor, string $remarks = 'Rejected'): bool
    {
        try {
            $status = self::codeDetail(ApprovalEnum::Rejected, $this->codeId);
            
            $result = $this->rejectAction(
                $actor,
                $status,
                'OrderID',
                $order->getKey(),
                $remarks,
                'DocStatus'
            );

            // Handle both boolean and array returns
            if (is_bool($result)) {
                return $result;
            }

            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            Log::error('Order rejection failed in workflow service', [
                'order_id' => $order->getKey(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Return an Order for modification
     */
    public function return(Order $order, User $actor, string $remarks = 'Returned for modification'): bool
    {
        try {
            $status = self::codeDetail(ApprovalEnum::Pending, $this->codeId);
            
            $result = $this->approveAction(
                $actor,
                $status,
                'OrderID',
                $order->getKey(),
                $remarks,
                'DocStatus'
            );

            // Handle both boolean and array returns
            if (is_bool($result)) {
                return $result;
            }

            return isset($result['success']) && $result['success'] === true;
        } catch (\Exception $e) {
            Log::error('Order return failed in workflow service', [
                'order_id' => $order->getKey(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get workflow history for an Order
     */
    public function getHistory(Order $order, int $limit = 1000)
    {
        try {
            return $this->historyData('OrderID', $limit)
                ->where('SourceID', $order->getKey())
                ->values();
        } catch (\Exception $e) {
            Log::error('Failed to fetch order workflow history', [
                'order_id' => $order->getKey(),
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Check if user can perform workflow action
     */
    public function canUserApprove(Order $order, User $user): bool
    {
        try {
            $pending = DB::select("
                SELECT COUNT(*) as count 
                FROM t_WorkFlowPending 
                WHERE Source = ? AND SourceID = ? AND UserId = ? AND DeletedOn IS NULL
            ", ['OrderID', $order->getKey(), $user->Id]);

            return ($pending[0]->count ?? 0) > 0;
        } catch (\Exception $e) {
            Log::error('Failed to check user approval permission', [
                'order_id' => $order->getKey(),
                'user_id' => $user->Id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get pending approvals for an Order
     */
    public function getPendingApprovals(Order $order): array
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Failed to fetch pending approvals', [
                'order_id' => $order->getKey(),
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Check if Order is fully approved
     */
    public function isFullyApproved(Order $order): bool
    {
        try {
            $pendingCount = DB::select("
                SELECT COUNT(*) as count 
                FROM t_WorkFlowPending 
                WHERE Source = ? AND SourceID = ? AND DeletedOn IS NULL
            ", ['OrderID', $order->getKey()]);

            return ($pendingCount[0]->count ?? 0) === 0;
        } catch (\Exception $e) {
            Log::error('Failed to check if order is fully approved', [
                'order_id' => $order->getKey(),
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}