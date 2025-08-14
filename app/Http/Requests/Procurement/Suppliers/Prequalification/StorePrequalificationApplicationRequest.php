<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use App\Enums\ThirdPartyTypeEnum;
use Illuminate\Foundation\Http\FormRequest;

class StorePrequalificationApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === ThirdPartyTypeEnum::Supplier;
    }

    public function rules(): array
    {
        return [
            'round_id' => ['required', 'integer', 'exists:t_PrequalificationRounds,RoundID'],
            'responses' => ['required', 'array'],
            'responses.*.criteria_id' => ['required', 'integer', 'exists:t_Criterias,CriteriaID'],
            'responses.*.response_text' => ['nullable', 'string'],
            'responses.*.file' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:2048'],
        ];
    }
}
