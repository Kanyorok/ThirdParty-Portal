<?php

namespace App\Services\Core;

use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
use App\Models\Procurement\Requisitions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentApprovalService
{
    public function __construct(protected ApprovalService $approvalService)
    {
        //
    }

    public function approve(Request $request, int $id)
    {
        $actor = $request->user();
        $data = $request->validated();
        $documentType = $data['document_type'];

        $docMap = [
            'purchase_order' => [
                'model' => Order::class,
                'route' => 'purchaseOrder.approval',
                'approved_column' => 'DocStatus',
                'approved_value' => 'a',
            ],
            'purchase_requisition' => [
                'model' => Requisitions::class,
                'route' => 'requisition.approval',
                'approved_column' => 'StatusID',
                'approved_value' => 26,
            ],
        ];

        $modelClass = $docMap[$documentType]['model'];
        $document = $modelClass::findOrFail($id);

        // Check if already fully approved
        if ($this->approvalService->isFullyApproved($documentType, $id, (float) $data['order_total'])) {
            return redirect()->route($docMap[$documentType]['route'], $id)
                ->with('warning', 'This document is already fully approved.');
        }

        $isNowFullyApproved = false;

        // Perform insert + update in a transaction
        DB::transaction(function () use (
            $documentType, $id, $actor, $data, $docMap, &$isNowFullyApproved
        ) {
            // Insert approval only if not already approved by this user
            $alreadyApproved = DB::table('t_Approvals')
                ->where('DocType', $documentType)
                ->where('DocumentId', $id)
                ->where('UserId', $actor->Id)
                ->exists();

            if (!$alreadyApproved) {
                DB::table('t_Approvals')->insert([
                    'DocType'     => $documentType,
                    'DocumentId'  => $id,
                    'UserId'      => $actor->Id,
                    'Status'      => 'approved',
                    'CreatedBy'   => $actor->Id,
                    'CreatedOn'   => now(),
                    'ModifiedBy'  => $actor->Id,
                    'ModifiedOn'  => now(),
                ]);
            }

            // After insert, check if this was the final approval
            $isNowFullyApproved = app(ApprovalService::class)
                ->isFullyApproved($documentType, $id, (float) $data['order_total']);

            if ($isNowFullyApproved) {
                // Perform the update via query builder instead of model (safe for transactions)
                DB::table((new $docMap[$documentType]['model'])->getTable())
                    ->where('id', $id)
                    ->update([
                        $docMap[$documentType]['approved_column'] => $docMap[$documentType]['approved_value'],
                    ]);
            }
        });

        // Redirect response based on approval outcome
        return redirect()->route($docMap[$documentType]['route'], $id)
            ->with($isNowFullyApproved ? 'success' : 'info', $isNowFullyApproved
                ? 'Document fully approved!'
                : 'Approval recorded, waiting for more approvers.');
    }

    public function approveDocument(string $docType, float $amount, User $actor, int $documentId): array
    {
        if ($this->approvalService->isFullyApproved($docType, $documentId, $amount)) {
            return [
                'status' => false,
                'message' => 'This document has already been fully approved.',
            ];
        }

        $this->recordApproval($docType, $documentId, $actor);

        return [
            'status' => true,
            'message' => 'Document approved successfully.',
        ];
    }

    private function recordApproval(string $docType, int $documentId, User $actor): void
    {
        DB::table('t_Approvals')->updateOrInsert(
            [
                'DocType' => $docType,
                'DocumentId' => $documentId,
                'UserId' => $actor->Id,
            ],
            [
                'Status' => 'approved',
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]
        );
    }

    private function recordRejection(string $docType, int $documentId, User $actor, ?string $reason): void
    {
        DB::table('t_Approvals')->updateOrInsert(
            [
                'DocType' => $docType,
                'DocumentId' => $documentId,
                'UserId' => $actor->Id,
            ],
            [
                'Status' => 'rejected',
                'RejectionReason' => $reason,
                'CreatedBy' => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]
        );
    }
}
