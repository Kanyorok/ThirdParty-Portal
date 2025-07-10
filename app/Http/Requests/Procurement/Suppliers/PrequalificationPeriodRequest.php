<?php

namespace App\Http\Requests\Procurement\Suppliers;

use App\Enums\Procurement\PrequalificationPeriodEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class PrequalificationPeriodRequest extends FormRequest
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
            'Title' => 'required|string',
            'Description' => 'nullable|string',
            'StartDate' => 'required|date_format:d/m/Y',
            'EndDate' => 'required|date_format:d/m/Y|after_or_equal:StartDate',
            'MaxVendors' => 'required|integer|min:1',
            'Status' => ['required', new Enum(PrequalificationPeriodEnum::class)],
        ];
    }
}
