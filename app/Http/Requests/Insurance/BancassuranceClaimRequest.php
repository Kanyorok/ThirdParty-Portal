<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceClaimRequest extends FormRequest
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
            'PolicyId' => 'required|exists:t_BancassurancePolicies,Id',
            'ClaimType' => 'required|exists:t_CodeDetails,ID',
            'ClaimReason' => 'required|string',
            'ClaimAmount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'ClaimDate' => 'required|date',
        ];
    }
}
