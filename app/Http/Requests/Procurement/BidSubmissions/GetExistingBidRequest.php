<?php

namespace App\Http\Requests\Procurement\BidSubmissions;

use App\Http\Requests\ApiRequest;

class GetExistingBidRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
            'third_party_id' => 'nullable|integer',
        ];
    }
}
