<?php

namespace App\Http\Requests\DMS;

use App\Enums\DMS\ContentEnum;
use App\Enums\DMS\StringComparisonEnum;
use App\Exceptions\ErroredException;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class TaggingRuleRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'Content' => ['required'],
            'Comparison' => ['required'],
            'Value' => ['required', 'string', 'max:250'],
        ];
    }

    public function messages(): array
    {
        return [
            'Content.required' => 'The comparison content field is required.',
            'Comparison.required' => 'The comparison type is required.',
            'Value.required' => 'The value field is required.',
            'Value.string' => 'The value must be a string.',
            'Value.max' => 'The value may not be greater than 250 characters.',
        ];
    }

    public function getComparisonType(): StringComparisonEnum
    {
        try {
            return StringComparisonEnum::fromValue($this->validated('Comparison'));
        } catch (ErroredException) {
        }

        throw ValidationException::withMessages([
            'Comparison' => 'Invalid comparison type provided.',
        ]);
    }

    public function getContentType(): ContentEnum
    {
        try {
            return ContentEnum::fromValue($this->validated('Content'));
        } catch (ErroredException) {
        }

        throw ValidationException::withMessages([
            'Content' => 'Invalid content type provided.',
        ]);
    }
}
