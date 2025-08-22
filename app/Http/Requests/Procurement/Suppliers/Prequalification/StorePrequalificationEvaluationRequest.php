<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Foundation\Http\FormRequest;


class StorePrequalificationEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('evaluator');
    }

    public function rules(): array
    {
        return [
            'ApplicationID' => ['required', 'exists:t_SupplierPrequalificationApplications,ApplicationID'],
            'SectionID' => ['required', 'exists:t_PrequalificationRoundSections,SectionID'],
            'CriteriaID' => ['required', 'exists:t_PrequalificationRoundCriteria,CriteriaID'],
            'Score' => ['required', 'numeric', 'min:0'],
            'MaxScore' => ['required', 'numeric', 'min:0'],
            'Remarks' => ['nullable', 'string'],
        ];
    }
}
