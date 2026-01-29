<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class MedicalFundPackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'Name' => ['required', 'string', 'max:100'],
            'CoverageDescription' => ['nullable', 'string', 'max:255'],
            'Premium' => ['required', 'numeric', 'min:0'],
            'IsCompulsory' => ['nullable', 'boolean'],

            // coverage pivot validation
            'coverage_ids' => ['array'],
            'coverage_ids.*' => ['integer'],
            'coverage.AnnualLimit.*' => ['nullable', 'numeric', 'min:0'],
            'coverage.PerVisitLimit.*' => ['nullable', 'numeric', 'min:0'],
            'coverage.WaitingPeriod.*' => ['nullable', 'integer', 'min:0'],
            'coverage.Scope.*' => ['nullable', 'in:PerBeneficiary,PerFamily'],
        ];
    }

    public function messages(): array
    {
        return [
            'Name.required' => 'The package name is required.',
            'Premium.required' => 'Please specify the premium amount.',
        ];
    }
}
