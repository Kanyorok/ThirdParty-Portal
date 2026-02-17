<?php

namespace App\Http\Requests\Procurement\Tenders;

use App\Http\Requests\ApiRequest;

class StoreTenderRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'tender_type' => 'required|string',
            'tender_category_id' => 'required|integer|exists:t_TenderCategories,Id',
            'item_category_id' => 'required|integer|exists:t_ItemCategories,Id',
            'submission_deadline' => 'required|date|after_or_equal:today',
            'opening_date' => 'required|date|after:submission_deadline',
            'currency_id' => 'required|integer|exists:t_Currencies,Id',
            'scope_of_work' => 'nullable|string',
            'instructions' => 'nullable|string',
            'procurement_mode_id' => 'nullable|integer|exists:t_ProcurementModes,Id',
            'start_date' => 'nullable|date',
            'status' => 'nullable|string',
        ];
    }
}
