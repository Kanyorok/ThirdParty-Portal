<?php

namespace App\Http\Requests\Procurement\prequalification;

use Illuminate\Foundation\Http\FormRequest;

class StoreCriteriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'CriteriaName' => ['required', 'string', 'max:255'],
            'Description' => ['nullable', 'string'],
            'SectionID' => ['required', 'exists:t_Sections,id'],
        ];
    }
}
