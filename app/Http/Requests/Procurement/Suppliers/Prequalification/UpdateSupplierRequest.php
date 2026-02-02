<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ThirdPartyName' => ['required', 'string', 'max:255'],
            'TradingName' => ['nullable', 'string', 'max:255'],
            'BusinessType' => ['required', 'exists:t_CodeDetails,Id'],
            'RegistrationNumber' => ['nullable', 'string', 'max:50'],
            'TaxPIN' => ['nullable', 'string', 'max:50'],
            'VATNumber' => ['nullable', 'string', 'max:50'],
            'Country' => ['required'], // ID is sent
            'PhysicalAddress' => ['nullable', 'string', 'max:255'],
            'Email' => ['required', 'email', 'max:255', Rule::unique('t_ThirdParties', 'Email')->ignore($this->route('supplier'))], // Fix ignore syntax too if needed, but 'ignore($this->route('supplier')->Id)' was correct if supplier is object. If route model binding, ->id works.
            'Phone' => ['nullable', 'string', 'max:20', 'regex:/^\+[1-9]\d{7,14}$/'],
            'Website' => ['nullable', 'url', 'max:255'],
            'IsPrequalified' => ['nullable', 'boolean'],
            'ApprovalStatus' => ['required', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:t_SupplierCategories,SupplierCategoryID'],
        ];
    }
}
