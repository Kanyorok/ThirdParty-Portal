<?php

namespace App\Http\Requests\Base;

use App\Enums\Core\ExtensionsEnum;
use App\Services\ImageService;
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
                           Rule::file()->types(ExtensionsEnum::getAllMimeTypes())->max(9000),
                          ],
               ];
    }


    public function save(string $Type, string $TypeId): ImageService
    {
        return ImageService::createUpload($this->file('file'), $Type, $TypeId, $this->user());
    }
}
