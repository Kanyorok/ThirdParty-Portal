<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class BidSubmissionResource extends JsonResource
{
    public function toArray($request): array
    {
        $encryptedDocs = json_decode($this->EncryptedDocuments ?? '', true);
        $documentCount = is_array($encryptedDocs) ? count($encryptedDocs) : 0;

        $bidStatus = $this->BidStatus ?? null;
        $accessStatus = $this->status ?? null;
        $mode = $this->getAttribute('status_mode') ?? 'access';
        $status = $mode === 'bid' ? $bidStatus : $accessStatus;

        return [
            'id' => $this->Id,
            'tender_id' => $this->getAttribute('tender_id'),
            'tender_ref' => $this->TenderRef,
            'tender_no' => $this->getAttribute('tender_no'),
            'tender_title' => $this->getAttribute('tender_title'),
            'supplier_id' => $this->SupplierId,
            'supplier_name' => $this->SupplierName,
            'bid_amount' => $this->BidAmount,
            'currency' => $this->Currency,
            'validity_period' => $this->ValidityPeriod,
            'delivery_period' => $this->DeliveryPeriod,
            'payment_terms' => $this->PaymentTerms,
            'status' => $status,
            'bid_status' => $bidStatus,
            'access_status' => $accessStatus,
            'submitted_at' => $this->CreatedOn?->toISOString(),
            'received_at' => $this->ReceivedAt?->toISOString(),
            'submission_source' => $this->SubmissionSource,
            'documents_count' => $documentCount,
            'can_access_documents' => $this->canAccessDocuments(),
            'bid_opening_date' => $this->BidOpeningDate,
            'remarks' => $this->Remarks,
            'envelope_status' => $this->EnvelopeStatus ?? null,
            'is_complete' => $this->IsComplete ?? null,
            'submission_reference' => $this->getAttribute('submission_reference'),
        ];
    }
}
