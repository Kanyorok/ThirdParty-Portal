<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CompetitorProductRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "Name" => ['required', 'string', 'max:200',],
            "Limit" => ['required', 'numeric', 'min:1', 'max:99999999999999'],
            "InterestRate" => ['required', 'numeric', 'min:0.01', 'max:100',],
            "OtherCharges" => ['required', 'numeric', 'min:0', 'max:99999999999999'],
            "RepaymentPeriod" => ['required', 'integer', 'min:1', 'max:5000'],
            "SecurityRequired" => ['nullable', 'string', 'max:200',],
            "Clients" => ['required', 'integer', 'min:0', 'max:99999999999999',],
            'Notes' => ['nullable', 'string', 'max:2000',],
        ];
    }
}
