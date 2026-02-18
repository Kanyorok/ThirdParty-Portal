<?php

namespace App\Http\Requests\Procurement\TenderClarifications;

use App\Http\Requests\ApiRequest;

class StoreTenderClarificationRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
            'question' => 'required|string|min:3|max:2000',
            'is_public' => 'nullable|boolean',
            'third_party_id' => 'nullable|integer',
        ];
    }
}
