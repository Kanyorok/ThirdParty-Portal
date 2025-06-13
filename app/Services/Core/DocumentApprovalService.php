<?php

namespace App\Services\Core;

use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Models\Auth\User;
use App\Models\Procurement\Order;
use App\Models\Procurement\Requisitions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentApprovalService
{
    public function __construct(protected ApprovalService $approvalService)
    {
        //
    }

    public function approve(ApproveOrderRequest $request, int $id): RedirectResponse
    {
        try {
            $actor = $request->user();
            $data = $request->validated();

            $documentType = $data['document_type'];
            $action = $data['action'] ?? 'approve';
            $orderTotal = (float) $data['order_total'];

            $docMap = [
                'purchase_order' => [
                    'model' => Order::class,
                    'route' => 'purchaseOrder.approval',
                ],
                'purchase_requisition' => [
                    'model' => Requisitions::class,
                    'route' => 'purchaseRequisition.approval',
                ],
            ];

            if (!isset($docMap[$documentType])) {
                return redirect()->back()->with('error', 'Invalid document type.');
            }

            $modelClass = $docMap[$documentType]['model'];
            $route = route($docMap[$documentType]['route'], $id);

            $document = $modelClass::findOrFail($id);

            // Prevent duplicate approval
            if ($this->approvalService->isFullyApproved($documentType, $id, $orderTotal)) {
                return redirect($route)->with('info', 'This document is already fully approved.');
            }

            if ($action === 'reject') {
                $this->recordRejection($documentType, $id, $actor, $request->input('rejection_reason'));
                $document->status = 'rejected';
                $document->save();

                return redirect($route)->with('status', 'Document rejected.');
            }

            $this->recordApproval($documentType, $id, $actor);

            if ($this->approvalService->isDocumentApproved($documentType, $orderTotal, $actor, $id)) {
                $document->DocStatus = 'a'; // Optional: Check if this column exists
                $document->save();

                return redirect($route)->with('status', 'Document approved successfully.');
            }

            return redirect($route)->with('status', 'Approval recorded, pending full approval.');

        } catch (\Throwable $e) {
            Log::error('Approval exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Error approving document.');
        }
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
                'DocType' => $docType,
                'DocumentId' => $documentId,
                'UserId' => $actor->Id,
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
