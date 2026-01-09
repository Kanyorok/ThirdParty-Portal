<?php

namespace App\Http\Requests\Procurement\prequalification;

use Illuminate\Foundation\Http\FormRequest;

class StoreSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'SectionName' => ['required', 'string', 'max:255', 'unique:t_Sections'],
            'Description' => ['nullable', 'string'],
        ];
    }
}
