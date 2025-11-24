<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class UOMConversionRequest extends FormRequest
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

            'Item' => 'required|exists:t_Items,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'AlternateUOM' => 'required|exists:t_UOM,Id',
            'ConversionFactor' => 'required|numeric|min:0',
            'Remarks' => 'nullable|string|max:500',

            //
        ];
    }
}
