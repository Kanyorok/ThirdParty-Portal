<?php

namespace App\Http\Requests\Procurement\Tenders;

use App\Http\Requests\ApiRequest;

class UpdateTenderRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'tender_type' => 'sometimes|required|string',
            'tender_category_id' => 'sometimes|required|integer|exists:t_TenderCategories,Id',
            'item_category_id' => 'sometimes|required|integer|exists:t_ItemCategories,Id',
            'submission_deadline' => 'sometimes|required|date|after_or_equal:today',
            'opening_date' => 'sometimes|required|date|after:submission_deadline',
            'currency_id' => 'sometimes|required|integer|exists:t_Currencies,Id',
            'scope_of_work' => 'nullable|string',
            'instructions' => 'nullable|string',
            'procurement_mode_id' => 'nullable|integer|exists:t_ProcurementModes,Id',
            'start_date' => 'nullable|date',
            'status' => 'nullable|string',
        ];
    }
}
