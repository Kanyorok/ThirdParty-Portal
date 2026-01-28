<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class MedicalFundDisbursementRequest extends FormRequest
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
            'FundId' => 'required|exists:t_MedicalFunds,Id',
            'ContributorId' => 'nullable|exists:t_MedicalFundContributors,Id',
            'BeneficiaryId' => 'required|exists:t_MedicalFundBeneficiaries,Id',
            'CoverageId' => 'nullable|exists:t_Coverages,Id',
            'PackageId' => 'nullable|exists:t_MedicalFundPackages,Id',
            'DisbursementDate' => 'required|date',
            'Amount' => 'required|numeric|min:0.01',
            'Purpose' => 'nullable|string|max:500',
        ];
    }
}
