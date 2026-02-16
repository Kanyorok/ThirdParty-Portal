<?php

namespace App\Http\Requests\Procurement\prequalification;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'CategoryID' => 'required|integer',
            'documents' => 'nullable|array',
            'documents.*.Description' => 'nullable|string',
            'documents.*.file' => 'required|file|max:10240',
        ];
    }
}
