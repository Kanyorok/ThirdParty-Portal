<?php

namespace App\Http\Requests\Procurement;


use App\Models\Inventory\ItemCategories;
use App\Models\Procurement\ConsolidatedProcurementPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            // Validate dynamically in withValidator to only enforce for checked needs
            'budget_line_id.*' => 'nullable',
        ];
    }

    /**
     * Add conditional validation ensuring each selected need has a valid budget line.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $selectedNeeds = $this->input('selected_needs', []);
            $budgetLinesByNeed = $this->input('budget_line_id', []);

            if (!is_array($selectedNeeds)) {
                $selectedNeeds = [];
            }
            if (!is_array($budgetLinesByNeed)) {
                $budgetLinesByNeed = [];
            }

            foreach ($selectedNeeds as $needId) {
                $needId = (int) $needId;
                $value = $budgetLinesByNeed[$needId] ?? null;

                if (empty($value)) {
                    $validator->errors()->add("budget_line_id.$needId", 'Please select a budget line for the checked need.');
                    continue;
                }

                $exists = DB::table('t_BudgetLines')
                    ->where('Id', (int) $value)
                    ->whereNull('DeletedOn')
                    ->exists();

                if (!$exists) {
                    $validator->errors()->add("budget_line_id.$needId", 'The selected budget line is invalid or inactive.');
                }
            }
        });
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
