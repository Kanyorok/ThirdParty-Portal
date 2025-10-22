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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
     public function rules(): array
    {
        return [
        'InsuranceProviderId' => 'required|exists:t_InsuranceProviders,Id',
        'Product'=>'required|exists:t_InsuranceProducts,Id',
            'RuleName' => [
                'required',
                'string',
                'max:100',
                Rule::unique(InsurancePricingRule::class, 'RuleName')
                    ->where(function ($query) {
                        return $query->where('InsuranceProviderId', $this->InsuranceProviderId)
                            ->where('Product', $this->Product);
                    }),
            ],
            'CoverageAmountMin' => 'required|integer|accepted_if:CoverageAmountMin,<=,CoverageAmountMax',
            'CoverageAmountMax' => 'required|integer|accepted_if:CoverageAmountMax,>=,CoverageAmountMin',
        'PremiumRate' => 'required|integer|min:0',
            'AgeMin' => 'required|integer|accepted_if:AgeMin,<=,AgeMax',
            'AgeMax' => 'required|integer|accepted_if:AgeMax,>,AgeMin',
            'TenureMin' => 'required|integer|accepted_if:TenureMin,<=,TenureMax',
            'TenureMax' => 'required|integer|accepted_if:TenureMax,>,TenureMin',
        'IsActive' => 'required|boolean|',
        ];
    }
}
