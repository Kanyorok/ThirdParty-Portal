<?php

namespace App\Http\Requests\Procurement;


use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class PlanFromNeedsRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plan_id' => 'required|integer|exists:t_ConsolidatedProcurementPlan,PlanID',
            'category_id' => 'nullable|integer|exists:t_ItemCategories,Id',
            'selected_needs' => 'required|array|min:1',
            'selected_needs.*' => 'required|integer|exists:t_DepartmentNeeds,Id',
            'budget_line_id' => 'required|array',
            'budget_line_id.*' => 'nullable|integer|exists:t_BudgetMaster,BudgetLineID',
        ];
    }

    public function getPlan(): ConsolidatedProcurementPlan
    {
        $plan = ConsolidatedProcurementPlan::find($this->validated('plan_id'));
        if ($plan instanceof ConsolidatedProcurementPlan) {
            return $plan;
        }

        throw ValidationException::withMessages(['plan_id' => 'Plan not found']);
    }

    public function getCategory(): ItemCategories
    {
        $category = ItemCategories::find($this->validated('category_id'));
        if ($category instanceof ItemCategories) {
            return $category;
        }

        throw ValidationException::withMessages(['category_id' => 'Category not found']);
    }
}
