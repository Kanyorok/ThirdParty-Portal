<?php

namespace App\Http\Requests\Insurance\ProviderAndProducts;

use Illuminate\Foundation\Http\FormRequest;

class InsurancePricingRuleRequest extends FormRequest
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
            'InsuranceProviderId' => 'required|exists:t_InsuranceProviders,Id',
            'Product' => 'required|exists:t_InsuranceProducts,Id',
            'RuleName' => 'required|string|max:100',
            'CoverageAmountMin' => 'required|integer|min:0',
            'CoverageAmountMax' => 'required|integer|min:0',
            'PremiumRate' => 'required|integer|min:0',
            'AgeMin' => 'required|integer|min:0',
            'AgeMax' => 'required|integer|min:0',
            'TenureMin' => 'required|integer|min:0',
            'TenureMax' => 'required|integer|min:0',
            'IsActive' => 'required|boolean|',
        ];
    }
}
