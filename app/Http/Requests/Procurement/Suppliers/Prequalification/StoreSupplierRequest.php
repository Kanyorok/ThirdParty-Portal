<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use App\Enums\BusinessTypeEnum;

class StoreSupplierRequest extends FormRequest
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
            'BusinessType' => ['required', new Enum(BusinessTypeEnum::class)],
            'RegistrationNumber' => ['nullable', 'string', 'max:50'],
            'TaxPIN' => ['nullable', 'string', 'max:50'],
            'VATNumber' => ['nullable', 'string', 'max:50'],
            'Country' => ['required', 'string', 'max:50'],
            'PhysicalAddress' => ['nullable', 'string', 'max:255'],
            'Email' => ['required', 'email', 'max:255', 'unique:t_ThirdParties,Email'],
            'Phone' => ['nullable', 'string', 'max:20'],
            'Website' => ['nullable', 'url', 'max:255'],
            'IsPrequalified' => ['nullable', 'boolean'],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['exists:t_SupplierCategories,SupplierCategoryID'],
        ];
    }
}
