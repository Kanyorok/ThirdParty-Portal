<?php

namespace App\Http\Requests\Procurement\BidSubmissions;

use App\Http\Requests\ApiRequest;

class ListBidSubmissionsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'third_party_id' => 'nullable|integer',
        ];
    }
}
