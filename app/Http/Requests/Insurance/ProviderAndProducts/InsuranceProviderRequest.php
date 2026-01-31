<?php

namespace App\Http\Requests\Insurance\ProviderAndProducts;

use App\Models\Insurance\InsuranceProvider;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'Name' => [
                'required',
                'string',
                'max:100',
                Rule::unique(InsuranceProvider::class, 'Name')
                    ->where(fn ($query) => $query->where('Country', $this->Country)),
            ],
            'Country' => 'required|string|max:50',
            'ContactPerson' => 'required|string|max:100',
            'Email' => 'required|email|max:100',
            'Phone' => ['required','string','max:20','regex:/^\+[1-9]\d{7,14}$/'],
            'IsActive' => 'nullable|boolean',
        ];
    }
}
