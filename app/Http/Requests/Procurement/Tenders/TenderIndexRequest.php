<?php

namespace App\Http\Requests\Procurement\Tenders;

use App\Http\Requests\ApiRequest;

class TenderIndexRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'status' => 'nullable|string',
            'tenderType' => 'nullable|string',
            'search' => 'nullable|string',
            'third_party_id' => 'nullable|integer',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }
}
