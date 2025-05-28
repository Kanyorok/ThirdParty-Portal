<?php

namespace App\Http\Requests\Procurement;

use App\Models\Procurement\Item;
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

    public function getItem(): Item
    {
        $item = Item::find($this->validated('ItemID'));
        if ($item instanceof Item) {
            return $item;
        }

        throw ValidationException::withMessages(['ItemID' => 'Item not found']);
    }
}
