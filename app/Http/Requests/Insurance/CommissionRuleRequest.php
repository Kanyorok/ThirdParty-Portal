<?php

namespace App\Http\Requests\Insurance;

use App\Models\Insurance\BancassuranceCommissionRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CommissionRuleRequest extends FormRequest
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
        'ProductId'   => 'required|exists:t_InsuranceProducts,Id',
        'PolicyTypeId'   => 'nullable|exists:t_CodeDetails,ID',
        'CommissionRate'   => 'required|numeric',
        'FixedAmount'   => 'required|numeric',
        'AppliesTo' => 'nullable|exists:t_CodeDetails,ID',
        'IsActive' => 'nullable|boolean',
        ];
    }
}
