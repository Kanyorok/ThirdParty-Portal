<?php

namespace App\Http\Requests\Procurement\Suppliers\Prequalification;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // guarded by auth middleware on routes
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480'], // 20MB
            'section_id' => ['required', 'integer', 'exists:t_Sections,Id'],
            'file_type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
