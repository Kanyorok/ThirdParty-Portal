<?php

namespace App\Http\Requests\Core;

use App\Enums\Core\VisibilityEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VisibilityRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'visibility' => ['required', Rule::enum(VisibilityEnum::class),],
        ];
    }

    public function getVisibility(): VisibilityEnum
    {
        return $this->enum('visibility', VisibilityEnum::class);
    }
}
