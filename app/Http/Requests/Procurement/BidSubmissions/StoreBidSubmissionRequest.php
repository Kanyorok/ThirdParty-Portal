<?php

namespace App\Http\Requests\Procurement\BidSubmissions;

use App\Http\Requests\ApiRequest;

class StoreBidSubmissionRequest extends ApiRequest
{
    public function rules(): array
    {
        $status = $this->input('status', 'draft');
        $isDraft = $status === 'draft';

        $rules = [
            'tender_id' => 'required|integer|exists:t_Tenders,Id',
            'bid_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|max:3',
            'validity_period' => 'required|integer|min:1',
            'delivery_period' => 'required|integer|min:1',
            'status' => 'required|in:draft,submitted',
            'payment_terms' => 'nullable|string|max:1000',
            'supplier_id' => 'nullable|integer|exists:t_Suppliers,Id',
            'third_party_id' => 'nullable|integer',
        ];

        if ($isDraft) {
            $rules['bid_documents.*'] = 'sometimes|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg';
        } else {
            $rules['bid_documents'] = 'required|array|min:1';
            $rules['bid_documents.*'] = 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'bid_documents.*.mimes' => 'Only PDF, Word, Excel, PNG, and JPEG files are allowed.',
            'bid_documents.*.max' => 'File size must not exceed 10 MB.',
            'bid_documents.required' => 'At least one document is required for final submissions.',
        ];
    }
}
