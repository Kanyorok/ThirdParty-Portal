<?php

namespace App\Services\Core;

use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
use App\Models\Procurement\Requisitions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            ],
            'purchase_requisition' => [
                'model' => Requisitions::class,
                'route' => 'requisition.approval',
            ],
        ];

        $modelClass = $docMap[$documentType]['model'];
        $route = route($docMap[$documentType]['route'], $id);

        $document = $modelClass::findOrFail($id);

        if ($this->approvalService->isFullyApproved($documentType, $id, (float)$data['order_total'])) {
            dd('❌ Document already fully approved (before recording anything)');
        }
        
        $this->recordApproval($documentType, $id, $actor);

        if ($this->approvalService->isFullyApproved($documentType, $id, (float)$data['order_total'])) {
            $document->DocStatus = 'a'; // 'a' for approved
            $document->save();
        
            dd('✅ Document is now fully approved and status updated');
        }
        
        dd('🕒 Approval recorded, waiting for more approvers');
        
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
                'DocType'    => $docType,
                'DocumentId'=> $documentId,
                'UserId'     => $actor->Id,
            ],
            [
                'Status'     => 'approved',
                'CreatedBy'  => $actor->Id,
                'ModifiedBy' => $actor->Id,
                'CreatedOn'  => now(),
                'ModifiedOn' => now(),
            ]
        );
    }

    private function recordRejection(string $docType, int $documentId, User $actor, ?string $reason): void
    {
        DB::table('t_Approvals')->updateOrInsert(
            [
                'DocType'    => $docType,
                'DocumentId'=> $documentId,
                'UserId'     => $actor->Id,
            ],
            [
                'Status'           => 'rejected',
                'RejectionReason'  => $reason,
                'CreatedBy'        => $actor->Id,
                'ModifiedBy'       => $actor->Id,
                'CreatedOn'        => now(),
                'ModifiedOn'       => now(),
            ]
        );
    }
}
