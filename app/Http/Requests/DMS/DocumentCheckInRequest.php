<?php

namespace App\Http\Requests\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Models\DMS\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DocumentCheckInRequest extends FormRequest
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
            'CheckInDocument' => ['required',
                Rule::file()->types(ExtensionsEnum::getAllMimeTypes())->max($size),
            ],
            'CheckInRemark' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function getFile(Document $document): UploadedFile
    {
        $file = $this->file('CheckInDocument');
        $mime = $file->getMimeType() ?? $file->getClientMimeType();
        if ($mime !== $document->ext()?->getMimeType()) {
            throw ValidationException::withMessages([
                'CheckInDocument' => 'file type should be same as uploaded.',
            ]);
        }

        return $file;
    }
}
