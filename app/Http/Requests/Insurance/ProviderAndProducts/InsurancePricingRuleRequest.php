<?php

namespace App\Http\Requests\Insurance\ProviderAndProducts;

use App\Models\Insurance\InsurancePricingRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     */
    public function rules(): array
    {
        return [
            'InsuranceProviderId' => [
                'required',
                'exists:t_InsuranceProviders,Id',
            ],

            'Product' => [
                'required',
                'exists:t_InsuranceProducts,Id',
            ],

            'RuleName' => [
                'required',
                'string',
                'max:100',
                Rule::unique(InsurancePricingRule::class, 'RuleName')
                    ->where(
                        fn ($query) =>
                        $query->where('InsuranceProviderId', $this->InsuranceProviderId)
                              ->where('Product', $this->Product)
                    ),
            ],

            /* ================= COVERAGE AMOUNT ================= */
            'CoverageAmountMin' => [
                'required',
                'integer',
                'min:1',
                'lte:CoverageAmountMax',
            ],

            'CoverageAmountMax' => [
                'required',
                'integer',
                'max:1000000000',
                'gte:CoverageAmountMin',
            ],

            /* ================= PREMIUM ================= */
            'CurrencyId' => [
                'Required',
                'exists:t_Currencies,Id',
            ],
            'PremiumRate' => [
                'required',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            /* ================= AGE LIMITS ================= */
            'AgeMin' => [
                'required',
                'integer',
                'min:0',
                'max:100',
                'lte:AgeMax',
            ],

            'AgeMax' => [
                'required',
                'integer',
                'min:0',
                'max:120',
                'gte:AgeMin',
            ],

            /* ================= TENURE (YEARS) ================= */
            'TenureMin' => [
                'required',
                'integer',
                'min:1',
                'lte:TenureMax',
            ],

            'TenureMax' => [
                'required',
                'integer',
                'max:100',
                'gte:TenureMin',
            ],

            /* ================= STATUS ================= */
            'IsActive' => [
                'required',
                'boolean',
            ],
        ];
    }
}
