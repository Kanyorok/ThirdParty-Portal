<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRateAndPricingRequest extends FormRequest
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
            'PropertyId' => ['required', 'exists:t_PropertyRegistry,Id'],
            'BlockId' => ['nullable', 'exists:t_PropertyBlock,Id'],
            'FloorId' => ['nullable', 'exists:t_PropertyFloor,Id'],
            'UnitId' => ['nullable', 'exists:t_PropertyUnit,Id'],
            'Rent' => ['required', 'numeric', 'min:0'],
            'ParkingFee' => ['nullable', 'numeric', 'min:0'],
            'ServiceCharge' => ['nullable', 'numeric', 'min:0'],
            'OtherCharges' => ['nullable', 'numeric', 'min:0'],
            'DepositAmount' => ['nullable', 'numeric', 'min:0'],
            'CurrencyId' => ['required', 'exists:t_Currencies,Id'],
            'TaxId' => ['required', 'exists:t_FinanceTaxRuleConfiguration,Id'],
        ];
    }
}
