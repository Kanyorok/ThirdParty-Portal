<?php

namespace App\Http\Requests\Marketing;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_label' => ['required', 'string', 'max:200'],
            'contact_phone' => ['nullable','required_without:contact_email', 'min:9', 'max:11', /*'regex: /^[(01)(07)]+[0-9]{9}$/i'*/],
            'contact_email' => ['nullable','required_without:contact_phone', 'email:rfc,dns', 'max:200'],
            'contact_note' => ['nullable', 'string', 'max:5000'],
        ];
    }


    public function savable(bool $update = false): array
    {
        return array_merge([
            'Label' => $this->validated('contact_label'),
            'Phone' => $this->validated('contact_phone'),
            'Email' => $this->validated('contact_email'),
            'Notes' => $this->validated('contact_note'),
            'ModifiedBy' => $this->user()->Id
        ], $update ? [] : ['CreatedBy' => $this->user()->Id]);
    }
}
