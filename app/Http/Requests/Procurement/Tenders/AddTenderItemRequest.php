<?php

namespace App\Http\Requests\Procurement\Tenders;

use App\Http\Requests\ApiRequest;

class AddTenderItemRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'item_id' => 'nullable|integer|exists:t_Items,Id|required_without:manual_description',
            'manual_description' => 'nullable|string|max:255|required_without:item_id',
            'qty_to_tender' => 'required|numeric|min:0.01',
            'plan_item_id' => 'nullable|integer',
            'item_category_id' => 'nullable|integer|exists:t_ItemCategories,Id',
            'source_type' => 'nullable|string|in:PLAN,MANUAL',
            'remarks' => 'nullable|string|max:255',
        ];
    }
}
