<?php

namespace App\Http\Requests\Suppliers\Prequalification;

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
            'sections' => ['required', 'array'],
            'sections.*.SectionId' => ['required', 'integer', 'exists:sections,Id'],
            'sections.*.Weight' => ['required', 'numeric', 'min:0', 'max:100'],
        ];
    }
}
