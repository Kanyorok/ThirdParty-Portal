<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceClaimPaymentRequest extends FormRequest
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
            'ClaimId' => 'required|exists:t_BancassuranceClaims,Id',
            'PaymentDate' => 'required|date',
            'PaymentAmount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'PaymentReference' => 'required|string',
            'Note' => 'nullable|string',
            'PaidBy' => 'required|string',
            'PaymentMethod' => 'required|exists:t_CodeDetails,ID'
        ];
    }
}
