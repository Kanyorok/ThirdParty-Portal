<?php

namespace App\Http\Requests\Procurement\prequalification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('section')->id;

        return [
            'SectionName' => ['required', 'string', 'max:255', 'unique:t_Sections,SectionName,' . $id],
            'Description' => ['nullable', 'string'],
        ];
    }
}
