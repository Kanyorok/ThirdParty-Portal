<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ThirdPartyTypeEnum;

class UpdatePrequalificationApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === ThirdPartyTypeEnum::Supplier;
    }

    public function rules(): array
    {
        return [
            'responses' => ['required', 'array'],
            'responses.*.criteria_id' => ['required', 'integer', 'exists:t_PrequalificationCriterias,CriteriaID'],
            'responses.*.response_text' => ['nullable', 'string'],
        ];
    }
}
