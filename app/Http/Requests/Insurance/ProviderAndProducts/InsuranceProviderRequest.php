<?php

namespace App\Http\Requests\Insurance\ProviderAndProducts;

use Illuminate\Foundation\Http\FormRequest;

class InsuranceProviderRequest extends FormRequest
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
        'Name' => 'required|string|max:100',
        'Country' => 'required|string|max:50',
        'ContactPerson' => 'required|string|max:100',
        'Email' => 'required|email|max:100',
        'Phone' => 'required|string|max:50',
        'IsActive' => 'nullable|boolean',
        ];
    }
}
