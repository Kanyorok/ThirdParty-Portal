<?php

namespace App\Http\Requests\Procurement;

use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PlanManualInputRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('put')) {
            return [
                'quantity' => 'sometimes|required|integer|min:1',
                'estimated_cost' => 'sometimes|required|numeric|min:0',
                'schedule_period' => 'sometimes|required|string|max:10',
                'expected_delivery_date' => 'sometimes|required|date',
                'budget_line_id' => 'sometimes|required|integer',
                'notes' => 'nullable|string|max:1000',
            ];
        }
        return [
            'PlanID' => 'required|exists:t_ConsolidatedProcurementPlan,PlanID',
            'ItemID' => 'required|exists:t_items,Id',
            'CategoryID' => 'required|exists:t_ItemCategories,Id',
            'quantity' => 'required|integer|min:1',
            'unit_of_measure' => 'required|string|max:50',
            'estimated_cost' => 'required|numeric|min:0',
            'schedule_period' => 'required|string|max:10',
            'expected_delivery_date' => 'required|date',
            'budget_line_id' => 'required|integer',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function getItem(): ItemMasterList
    {
        $item = ItemMasterList::find($this->validated('ItemID'));
        if ($item instanceof ItemMasterList) {
            return $item;
        }

        throw ValidationException::withMessages(['ItemID' => 'Item not found']);
    }

    public function getCategory(): ItemCategories
    {
        $category = ItemCategories::find($this->validated('CategoryID'));
        if ($category instanceof ItemCategories) {
            return $category;
        }

        throw ValidationException::withMessages(['CategoryID' => 'Category not found']);
    }
}
