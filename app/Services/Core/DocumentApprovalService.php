<?php

namespace App\Services\Core;

use App\Http\Requests\Orders\ApproveOrderRequest;
use App\Models\Procurement\Order;
use App\Models\Procurement\Requisitions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DocumentApprovalService
{
    /**
     * Create a new class instance.
     */
    public function __construct(protected ApprovalService $approvalService)
    {
        //

    }

    public function approve(ApproveOrderRequest $orderRequest, $id): RedirectResponse
    {
        try {
            $actor = $orderRequest->user();
            $validatedData = $orderRequest->validated();

            $action = $validatedData['action'] ?? 'approve';
            $orderTotal = (float)$validatedData['order_total'];
            $documentType = $validatedData['document_type'];

            // Map document types to model classes and routes
            $docMap = [
                'purchase_order' => [
                    'model' => Order::class,
                    'route' => 'purchaseOrder.approval',
                ],
                'purchase_requisition' => [
                    'model' => Requisitions::class,
                    'route' => 'purchaseRequisition.approval',
                ],
                // Add more document types as needed
            ];

            if (!isset($docMap[$documentType])) {
                return redirect()->back()->with('error', 'Invalid document type provided.');
            }

            $modelClass = $docMap[$documentType]['model'];
            $routeName = $docMap[$documentType]['route'];

            // Fetch the document model dynamically
            $document = $modelClass::findOrFail($id);
            $routePath = route($routeName, $document->Id);

            // Handle rejection
            if ($action === 'reject') {
                DB::table('t_Approvals')->updateOrInsert(
                    [
                        'DocType' => $documentType,
                        'DocumentId' => $document->Id,
                        'UserId' => $actor->Id,
                    ],
                    [
                        'RejectionReason' => $orderRequest->input('rejection_reason'),
                        'Status' => 'rejected',
                        'CreatedBy' => $actor->Id,
                        'ModifiedBy' => $actor->Id,
                        'CreatedOn' => now(),
                        'ModifiedOn' => now(),
                    ]
                );

                $document->status = 'rejected';
                $document->save();

                return redirect($routePath)->with('status', 'Document rejected.');
            }

            // Record approval
            DB::table('t_Approvals')->updateOrInsert(
                [
                    'DocType' => $documentType,
                    'DocumentId' => $document->Id,
                    'UserId' => $actor->Id,
                ],
                [
                    'CreatedBy' => $actor->Id,
                    'ModifiedBy' => $actor->Id,
                    'CreatedOn' => now(),
                    'ModifiedOn' => now(),
                ]
            );

            // Check if fully approved
            $isApproved = $this->approvalService->isDocumentApproved(
                $documentType,
                $orderTotal,
                $actor,
                $document->Id
            );

            if ($isApproved) {
                $document->DocStatus = 'a'; // optional: conditionally check if `DocStatus` exists
                $document->save();

                return redirect($routePath)->with('status', 'Document approved successfully.');
            }

            return redirect($routePath)->with('status', 'Approval recorded, pending full approval.');

        } catch (\Throwable $e) {
            Log::error('Exception occurred while approving document.', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Failed to approve document: ' . $e->getMessage());
        }
    }
}
