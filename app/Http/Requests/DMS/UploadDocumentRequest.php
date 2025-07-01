<?php

namespace App\Http\Requests\DMS;

use App\Enums\Core\ExtensionsEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UploadDocumentRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                Rule::file()->types(ExtensionsEnum::getAllMimeTypes())->max(9000),//todo filesize
            ],
        ];
    }

    public function message(): array
    {
        return [
            'file.required' => 'A file must be uploaded.',
            'file.types' => 'The uploaded file is not allowed .',
            'file.max' => 'The uploaded file must not exceed the maximum size of 9MB.',//todo filesize
        ];

    }
}
