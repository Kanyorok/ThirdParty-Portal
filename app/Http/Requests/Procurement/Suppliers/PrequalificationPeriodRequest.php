<?php

namespace App\Http\Requests\Procurement\Suppliers;

use Illuminate\Foundation\Http\FormRequest;

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
        'StartDate' => 'required|date',
        'EndDate' => 'required|date'
        ];
    }
}
