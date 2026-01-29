<?php

namespace App\Http\Requests\DMS;

use App\Enums\Core\VisibilityEnum;
use App\Models\DMS\Document;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class NewTagRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            "Name" => ['required', 'string', 'max:255'],
            "Description" => ['nullable', 'string', 'max:5000'],
            'Visibility' => ['required', Rule::enum(VisibilityEnum::class)],
            "Document" => ['nullable', 'string'],
        ];
    }

    public function getDocument(): ?Document
    {
        if ($this->has('Document')) {
            $document = Document::query()->where('DocumentId', $this->string('Document'))->first();
            if ($document instanceof Document) {
                return $document;
            }
        }

        return null;
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
}
