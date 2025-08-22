<?php

namespace App\Http\Requests\Insurance;

use App\Enums\Insurance\InsuranceReferralStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class BancAssuranceReferralRequest extends FormRequest
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
        'ClientName' => 'required|string',
        'ClientIDNumber' => 'required|String',
        'ClientPhone'   => 'required|string',
        'ClientEmail'   => 'required|email',
        'ReferredBy'    => 'nullable|exists:t_Users,Id',
        'ReferralDate'  => 'nullable|date',
        'InsuranceProductId'   => 'nullable|exists:t_InsuranceProducts,Id',
        'PreferredInsurerId' => 'required|exists:t_InsuranceProviders,Id',
        'Remarks' => 'nullable|string',
        'AssignedTo' => 'nullable|exists:t_Employees,Id',
        ];
    }
}
