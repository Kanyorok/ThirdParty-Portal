<?php

namespace App\Http\Requests\Procurement\TenderInvitations;

use App\Http\Requests\ApiRequest;

class RespondTenderInvitationRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
            'response_status' => 'required|string|in:accepted,declined,pending',
            'decline_reason' => 'nullable|string|max:1000',
            'third_party_id' => 'nullable|integer',
        ];
    }
}
