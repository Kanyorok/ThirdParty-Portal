<?php

namespace App\Http\Requests\Procurement\BidSubmissions;

use App\Http\Requests\ApiRequest;

class LegacyBidSubmissionRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
            'third_party_id' => 'required|integer|exists:t_ThirdParties,Id',
            'bid_documents' => 'required|array|min:1',
            'bid_documents.*' => 'file|mimes:pdf,doc,docx,zip|max:10240',
            'submission_notes' => 'nullable|string|max:500',
        ];
    }
}
