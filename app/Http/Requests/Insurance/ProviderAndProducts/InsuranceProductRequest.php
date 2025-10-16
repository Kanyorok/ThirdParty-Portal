<?php

namespace App\Http\Requests\Insurance\ProviderAndProducts;

use App\Models\Insurance\InsuranceProduct;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InsuranceProductRequest extends FormRequest
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
            'InsuranceProviderID' => 'required|exists:t_InsuranceProviders,Id',
            'Name' => [
            'required',
            'string',
            'max:50',
            Rule::unique(InsuranceProduct::class, 'Name')
                ->where(function ($query) {
                    return $query->where('InsuranceProviderID', $this->InsuranceProviderID)
                                 ->where('Type', $this->Type);
                }),
            ],
            'Type' => 'required|exists:t_CodeDetails,ID',
            'Description' => 'nullable|string|max:100',
            'IsActive' => 'nullable|boolean',
        ];
    }
}
