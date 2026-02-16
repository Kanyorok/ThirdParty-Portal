<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class MedicalFundBeneficiaryRequest extends FormRequest
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
            'FullName' => 'required|string',
            'Relationship' => 'required|exists:t_CodeDetails,ID',
            'DateOfBirth' => 'nullable|date',
            'NationalID' => 'nullable|string|max:50',
            'Contact' => 'nullable|string|max:50',
            'IsActive' => 'nullable|boolean',
        ];
    }
}
