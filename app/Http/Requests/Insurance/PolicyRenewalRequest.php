<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class PolicyRenewalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You could add role/permission checks here if needed
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
            'PolicyID'     => 'required', 'exists:t_BancassurancePolicies,Id',
            'RenewalDate'  => 'required', 'date',
            'NewStartDate' => 'required', 'date', 'after_or_equal:RenewalDate',
            'NewEndDate'   => 'required', 'date', 'after:NewStartDate',
            'Notes'        => 'nullable', 'string', 'max:500',
        ];
    }

    /**
     * Customize error messages (optional).
     */
    public function messages(): array
    {
        return [
            'PolicyID.required' => 'A policy must be selected for renewal.',
            'NewEndDate.after'  => 'The new end date must be after the new start date.',
        ];
    }
}
