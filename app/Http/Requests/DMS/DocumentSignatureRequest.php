<?php

namespace App\Http\Requests\DMS;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\VisibilityEnum;
use App\Enums\DMS\ImageGravityEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class DocumentSignatureRequest extends FormRequest
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
                'nullable', Rule::file()->types(ExtensionsEnum::Png->getMimeType())->max(9000),//todo filesize
            ],
            "Name" => ['required', 'string', 'max:200'],
            'Visibility' => ['required', Rule::enum(VisibilityEnum::class)],
            "Horizontal" => ['required', 'integer', 'min:1', 'max:5000'],
            "Vertical" => ['required', 'integer', 'min:1', 'max:10000'],
            "Opacity" => ['required', 'integer', 'min:0', 'max:100'],
            "Height" => ['required', 'integer', 'min:10', 'max:10000'],
            "Width" => ['required', 'integer', 'min:10', 'max:10000'],
            "Content" => ['required', 'string', 'max:200'],
            "ContentColour" => ['required', 'string', 'max:200'],
            "ContentSize" => ['required', 'integer', 'min:1', 'max:100'],
            "ContentPosition" => ['required', Rule::enum(ImageGravityEnum::class)],
            "ContentBorderColour" => ['required', 'string', 'max:200'],
            "ContentBorderWeight" => ['required', 'integer', 'min:0', 'max:50'],
        ];
    }

    /**
     * @throws ValidationException
     */
    public function getVisibility(): VisibilityEnum
    {
        $Visibility = $this->enum('Visibility', VisibilityEnum::class);
        if ($Visibility instanceof VisibilityEnum) {
            return $Visibility;
        }
        throw ValidationException::withMessages(['Visibility' => 'invalid visibility type']);
    }

    public function getContentPosition(): ImageGravityEnum
    {
        $ContentPosition = $this->enum('Visibility', ImageGravityEnum::class);
        if ($ContentPosition instanceof ImageGravityEnum) {
            return $ContentPosition;
        }
        throw ValidationException::withMessages(['ContentPosition' => 'invalid Content Position']);
    }
}
