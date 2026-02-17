<?php

namespace App\Http\Requests\Procurement\TenderInvitations;

use App\Http\Requests\ApiRequest;

class ListTenderInvitationsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'third_party_id' => 'nullable|integer',
            'supplier_id' => 'nullable|integer|exists:t_Suppliers,Id',
            'tender_id' => 'nullable|integer|exists:t_Tenders,Id',
            'status' => 'nullable|string|in:accepted,declined,pending',
        ];
    }
}
