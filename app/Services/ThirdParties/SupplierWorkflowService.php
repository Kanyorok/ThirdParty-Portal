<?php

namespace App\Services\ThirdParties;

use App\Enums\ThirdParty\ThirdPartyApprovalStatusEnum;
use App\Enums\ThirdParty\ThirdPartyStatusEnum;
use App\Models\Auth\User;
use App\Models\ThirdParty\SupplierMaster;
use App\Services\Core\ApprovalWorkflowService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class SupplierWorkflowService extends ApprovalWorkflowService
{
    public const CODE_ID = 'ThirdPartyApprovalStatus';

    /**
     * Submit a Supplier for approval
     */
    public function submit(SupplierMaster $supplier, User $actor, string $remarks = 'Submitted'): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($supplier, $actor, $remarks) {
            $supplier->ApprovalStatus = ThirdPartyApprovalStatusEnum::Submitted;
            $supplier->save();

            $status = self::codeDetail(ThirdPartyApprovalStatusEnum::Submitted, self::CODE_ID);

            return $this->submittedAction(
                $actor,
                $status,
                $supplier,
                SupplierMaster::getPrimaryKey(),
                $supplier->getKey(),
                $remarks
            );
        });
    }

    /**
     * Approve a Supplier
     */
    public function approve(SupplierMaster $supplier, User $actor, string $remarks = 'Approved', string $statusColumn = 'ApprovalStatus'): bool
    {
        $status = self::codeDetail(ThirdPartyApprovalStatusEnum::Approved, self::CODE_ID);

        $result = $this->approveAction(
            $actor,
            $status,
            SupplierMaster::getPrimaryKey(),
            $supplier->getKey(),
            $remarks,
            $statusColumn
        );

        if ($result) {
            $supplier->ApprovalStatus = ThirdPartyApprovalStatusEnum::Approved;
            $supplier->save();

            // Activate associated ThirdPartyUsers
            // Activate associated ThirdPartyUsers
            // 1. Verify email if not already verified (auto-verify for backend-approved suppliers)
            \App\Models\ThirdParty\ThirdPartyUser::where('ThirdPartyId', $supplier->ThirdPartyId)
                 ->whereNull('EmailVerifiedOn')
                 ->update(['EmailVerifiedOn' => now()]);

            // 2. Ensure account is active
            \App\Models\ThirdParty\ThirdPartyUser::where('ThirdPartyId', $supplier->ThirdPartyId)
                ->update(['IsActive' => true]);

            if ($thirdParty = $supplier->thirdParty) {
                try {
                    app(ThirdPartyStatusService::class)->applyStatus($thirdParty, ThirdPartyStatusEnum::Active);
                } catch (\Throwable $exception) {
                    Log::error('Failed to sync third-party approval status', [
                        'third_party_id' => $thirdParty->Id,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * Reject a Supplier
     */
    public function reject(SupplierMaster $supplier, User $actor, string $remarks = 'Rejected', string $statusColumn = 'ApprovalStatus'): bool
    {
        $status = self::codeDetail(ThirdPartyApprovalStatusEnum::Rejected, self::CODE_ID);

        $result = $this->rejectAction(
            $actor,
            $status,
            SupplierMaster::getPrimaryKey(),
            $supplier->getKey(),
            $remarks,
            $statusColumn
        );

        if ($result) {
            $supplier->ApprovalStatus = ThirdPartyApprovalStatusEnum::Rejected;
            $supplier->save();
            if ($thirdParty = $supplier->thirdParty) {
                try {
                    app(ThirdPartyStatusService::class)->applyStatus($thirdParty, ThirdPartyStatusEnum::Suspended);
                } catch (\Throwable $exception) {
                    Log::error('Failed to sync third-party rejection status', [
                        'third_party_id' => $thirdParty->Id,
                        'exception' => $exception->getMessage(),
                    ]);
                }
            }
        }

        return $result;
    }

    /**
     * Get workflow history for a specific Supplier
     */
    public function historyForSupplier(SupplierMaster $supplier): Collection
    {
        return $supplier->workflowHistory()
            ->with(['creator', 'status', 'stage'])
            ->get();
    }

    /**
     * Check if a user can approve a specific Supplier
     */
    public function canApproveSupplier(SupplierMaster $supplier, User $actor): bool
    {
        return parent::canApprove(
            SupplierMaster::getPrimaryKey(),
            $supplier->getKey(),
            $actor
        );
    }
}
