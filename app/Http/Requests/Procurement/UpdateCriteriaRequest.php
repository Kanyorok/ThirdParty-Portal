<?php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriteriaRequest extends FormRequest
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
        ];
    }
}

<?
php

namespace App\Http\Requests\Procurement;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCriteriaRequest extends FormRequest
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
        ];
    }
}
