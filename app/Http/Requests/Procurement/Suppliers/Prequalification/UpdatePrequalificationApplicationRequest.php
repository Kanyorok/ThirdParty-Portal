<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\ThirdPartyTypeEnum;

class UpdatePrequalificationApplicationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() && $this->user()->type === ThirdPartyTypeEnum::Supplier;
    }

    public function rules(): array
    {
        return [
            'responses' => ['required', 'array'],
            'responses.*.criteria_id' => ['required', 'integer', 'exists:t_Criterias,CriteriaID'],
            'responses.*.response_text' => ['nullable', 'string'],
            'responses.*.file' => ['sometimes', 'nullable', 'file', 'mimes:pdf,doc,docx,jpg,png', 'max:2048'],
        ];
    }
}
