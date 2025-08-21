<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use Illuminate\Foundation\Http\FormRequest;

class PropertyAttachmentsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'PropertyID' => 'required|exists:t_PropertyRegistry,Id',
            'DocumentTitle' => 'required|string|max:100',
            'DocumentType' => 'required|exists:t_CodeDetails,ID',
            'Description' => 'nullable|string|max:255',
        ];
    }
}
