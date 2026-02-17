<?php

namespace App\Http\Requests\Procurement\TenderClarifications;

use App\Http\Requests\ApiRequest;

class RespondTenderClarificationRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'answer' => 'required|string|min:10|max:2000',
            'is_published_to_all' => 'nullable|boolean',
            'responded_by' => 'required|string|max:255',
        ];
    }
}
