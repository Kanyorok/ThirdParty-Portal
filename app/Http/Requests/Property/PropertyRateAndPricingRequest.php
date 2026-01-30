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
            'Rent' => ['required', 'numeric', 'min:1'],
            'ParkingFee' => ['nullable', 'numeric', 'min:0'],
            'ServiceCharge' => ['nullable', 'numeric', 'min:0'],
            'OtherCharges' => ['nullable', 'numeric', 'min:0'],
            'DepositAmount' => ['nullable', 'numeric', 'min:0'],
            'CurrencyId' => [
                'required',
                'exists:t_Currencies,Id',
                function ($attribute, $value, $fail) {
                    $query = \DB::table('t_PropertyRateAndPricing')
                        ->where('PropertyId', $this->PropertyId)
                        ->where('BlockId', $this->BlockId)
                        ->where('FloorId', $this->FloorId)
                        ->where('UnitId', $this->UnitId)
                        ->where('CurrencyId', $value);

                    // Exclude current record during update
                    if ($this->route('Id')) {
                        $query->where('Id', '!=', $this->route('Id'));
                    }

                    if ($query->exists()) {
                        $fail('This combination of property, block, floor, unit, and currency already exists.');
                    }
                },
            ],
            'TaxId' => ['required', 'exists:t_FinanceTaxRuleConfiguration,Id'],
        ];
    }

}
