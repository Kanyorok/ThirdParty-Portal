<?php

namespace App\Http\Requests\Core;

use App\Enums\Core\ExtensionsEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CsvUploadRequest extends FormRequest
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
                           Rule::file()->extensions(ExtensionsEnum::Csv->value)->max('15mb'),
                          ],
               ];
    }

    public function getFile(): UploadedFile
    {
        $file = $this->file('file');
        if ($file instanceof UploadedFile && $file->isValid() && $file->getMimeType() === ExtensionsEnum::Csv->getMimeType()) {
            return $file;
        }

        throw ValidationException::withMessages(['file' => 'file is not a valid file']);
    }
}
