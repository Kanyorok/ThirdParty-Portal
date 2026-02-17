<?php

namespace App\Http\Requests\Procurement\TenderClarifications;

use App\Http\Requests\ApiRequest;

class ListPendingTenderClarificationsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'tender_id' => 'nullable|integer|exists:t_Tenders,Id',
            'third_party_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer|exists:t_Suppliers,Id',
        ];
    }
}
