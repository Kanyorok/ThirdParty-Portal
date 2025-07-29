<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

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
        'RefferedBy'    => 'required|exists:t_Employees,Id',
        'ReferralDate'  => 'required|date',
        'InsuranceProdeuctId'   => 'nullable|exists:t_CodeDetails,ID',
        'PreferredInsurerId' => 'required|exists:t_CodeDetails,ID',
        'Remarks' => 'nullable|string',
        'Status' => 'required|permissionEnum::InsuranceReferralStatus',
        'AssignedTo' => 'nullable|exists:t_Employees,Id',
        ];
    }
}
