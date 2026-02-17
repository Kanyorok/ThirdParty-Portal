<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class MedicalFundContributionRequest extends FormRequest
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
            'ContributorType' => 'required|exists:t_CodeDetails,ID',
            'ContributorId' => 'nullable|exists:t_ThirdParties,Id', // adjust if needed
            'Amount' => 'required|numeric|min:0.01',
            'ContributionDate' => 'required|date',
            'Notes' => 'nullable|string|max:500',
        ];
    }
}
