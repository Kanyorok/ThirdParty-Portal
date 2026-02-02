<?php

namespace App\Http\Requests\Budget;

use Illuminate\Foundation\Http\FormRequest;

class ReallocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        // allow all authenticated users (or add your own logic)
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'BudgetID' => ['required', 'exists:t_Budgets,Id'],
            'ReallocationType' => ['required', 'in:Branch,Department,Cross-Department'],
        ];

        $type = $this->input('ReallocationType');

        if ($type === 'Branch') {
            $rules['BranchID'] = ['required', 'exists:t_Branches,Id'];
        }

        if ($type === 'Department') {
            $rules['DepartmentID'] = ['required', 'exists:t_Departments,Id'];
        }

        if ($type === 'Cross-Department') {
            $rules['DepartmentID'] = ['required', 'exists:t_Departments,Id'];
            $rules['ToDepartmentID'] = ['required', 'different:DepartmentID', 'exists:t_Departments,Id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'BudgetID.required' => 'Please select a budget.',
            'ReallocationType.required' => 'Please choose a reallocation type.',
            'BranchID.required' => 'Branch selection is required for branch reallocations.',
            'DepartmentID.required' => 'Please select a source department.',
            'ToDepartmentID.required' => 'Please select a target department.',
            'ToDepartmentID.different' => 'The target department must be different from the source department.',
        ];
    }
}
