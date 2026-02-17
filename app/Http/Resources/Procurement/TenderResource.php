<?php

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

class TenderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'Id' => $this->Id,
            'TenderNo' => $this->TenderNo,
            'Title' => $this->Title,
            'TenderType' => $this->TenderType,
            'TenderCategory' => $this->TenderCategory,
            'ScopeOfWork' => $this->ScopeOfWork,
            'Instructions' => $this->Instructions,
            'SubmissionDeadline' => $this->SubmissionDeadline,
            'OpeningDate' => $this->OpeningDate,
            'Status' => $this->Status,
            'ProcurementModeId' => $this->ProcurementModeId,
            'StartDate' => $this->StartDate,
            'CurrencyId' => $this->CurrencyId,
            'currency_code' => $this->currency?->Code ?? null,
            'ApprovalRemarks' => $this->ApprovalRemarks,
            'ApprovalStatus' => $this->ApprovalStatus,
            'ItemCategoryId' => $this->ItemCategoryId,
            'CreatedBy' => $this->CreatedBy,
            'CreatedOn' => $this->CreatedOn,
            'ModifiedBy' => $this->ModifiedBy,
            'ModifiedOn' => $this->ModifiedOn,
            'DeletedBy' => $this->DeletedBy,
            'DeletedOn' => $this->DeletedOn,
            'procurementMode' => $this->whenLoaded('procurementMode'),
            // currency details omitted; use currency_code above
            'tenderCategoryRelation' => $this->whenLoaded('tenderCategoryRelation'),
            'itemCategoryRelation' => $this->whenLoaded('itemCategoryRelation'),
            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(function ($document) {
                    $documentId = $document->DocumentId ?? $document->getAttribute('DocumentId');
                    $data = $document->toArray();
                    $data['downloadUrl'] = $documentId
                        ? url('/api/v1/supplier/tenders/' . $this->Id . '/documents/' . $documentId . '/download')
                        : null;

                    return $data;
                });
            }),
            'items' => $this->whenLoaded('items', function () {
                return TenderItemResource::collection($this->items);
            }, []),
        ];
    }
}
