<?php

namespace App\Http\Requests\ProductDev;

use Illuminate\Foundation\Http\FormRequest;

class ProductDevelopmentFeatureRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'feature_title' => ['required', 'string', 'max:100'],
            'feature_content' => ['required', 'string', 'max:250'],
        ];
    }
}
