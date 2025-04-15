<?php

namespace App\Http\Requests\Base;

use Illuminate\Foundation\Http\FormRequest;

class Select2Request extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                'q' => [
                        'nullable',
                        'string',
                        'max:255',
                       ],
               ];
    }

    public function getSearchString(): ?string
    {
        $q = $this->validated('q');
        if (!is_string($q)) {
            return null;
        }

        return str_replace(['*', '%'], ['', ''], $q);
    }
}
