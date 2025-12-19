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
        $size = (int)bcmul(config('app.dms.file_size'), 1024, 0);
        return [
            'file' => [
                'required', 'bail',
                Rule::file()->types(ExtensionsEnum::getAllMimeTypes())->max($size),
            ],
        ];
    }

    public function message(): array
    {
        return [
            'file.required' => 'A file must be uploaded.',
            'file.mimetypes' => 'The type of file you uploaded is not permitted.',
            'file.types' => 'The uploaded file is not allowed .',
            'file.max' => 'The uploaded file must not exceed the maximum size of ' . config('app.dms.file_size') . 'MB.',
        ];

    }
}
