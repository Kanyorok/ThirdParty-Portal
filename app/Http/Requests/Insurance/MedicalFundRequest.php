<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class MedicalFundRequest extends FormRequest
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
            'FundName'      => 'required|string',
            'ProviderId'    => 'required|exists:t_InsuranceProviders,Id',
            'CoverageType'  => 'nullable|exists:t_CodeDetails,ID',
            'CoverageLimit' => 'required|numeric',
            'Description'   => 'nullable|string',
            'IsActive'      => 'required|boolean',
        ];
    }
}
