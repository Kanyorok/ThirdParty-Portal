<?php

namespace App\Http\Requests\Insurance;

use App\Models\Insurance\BancassuranceCommissionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommissionRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Clean input before validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('FixedAmount')) {
            $this->merge([
                'FixedAmount' => str_replace(',', '', $this->input('FixedAmount')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'RuleName' => [
                'required',
                'string',
                Rule::unique(BancassuranceCommissionRule::class, 'RuleName')
                    ->ignore($this->route('Id'), 'Id')
                    ->where(fn($query) => $query
                        ->where('ProductId', $this->ProductId)
                        ->where('PolicyTypeId', $this->PolicyTypeId)
                    ),
            ],
            'ProductId' => 'required|exists:t_InsuranceProducts,Id',
            'PolicyTypeId' => 'nullable|exists:t_CodeDetails,ID',
            'CommissionRate' => 'required|numeric',
            'FixedAmount' => 'required|numeric',
            'CurrencyId' => 'required|exists:t_Currencies,Id',
            'AppliesTo' => 'nullable|exists:t_CodeDetails,ID',
            'IsActive' => 'nullable|boolean',
        ];
    }
}
