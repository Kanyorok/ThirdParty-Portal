<?php

namespace App\Http\Requests\Insurance\ProviderAndProducts;

use App\Models\Insurance\InsuranceProductRider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InsuranceProductRiderRequest extends FormRequest
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
            'RiderName' => [
                'required',
                'string',
                'max:100',
                Rule::unique(InsuranceProductRider::class, 'RiderName')
                    ->where(function ($query) {
                        return $query->where('InsuranceProviderId', $this->InsuranceProviderId)
                            ->where('Product', $this->Product);
                    }),
            ],
            'Description' => 'nullable|string|max:255',
            'AdditionalPremium' => 'required|numeric|min:0',
            'IsOptional' => 'nullable|boolean',
            'IsActive' => 'nullable|boolean',
        ];
    }
}
