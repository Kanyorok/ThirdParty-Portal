<?php

namespace App\Http\Requests\Insurance\ProviderAndProducts;

use Illuminate\Foundation\Http\FormRequest;

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
            'Name' => 'required|string|max:50',
            'Type' => 'required|string|max:100',
            'Description' => 'required|string|max:100',
            'IsActive' => 'nullable|boolean',
        ];
    }
}
