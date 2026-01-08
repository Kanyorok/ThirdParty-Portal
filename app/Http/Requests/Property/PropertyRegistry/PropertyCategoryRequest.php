<?php

namespace App\Http\Requests\Property\PropertyRegistry;

use App\Models\Core\CategoryMaster;
use Illuminate\Foundation\Http\FormRequest;

class PropertyCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'Name' => [
                'required',
                'string',
                'max:255',
                function ($attribute, $value, $fail) {
                    $exists = CategoryMaster::where('Name', $value)
                        ->where('Code', '500000')
                        ->exists();

                    if ($exists) {
                        $fail('The category "' . $value . '" already exists.');
                    }
                },
            ],
            'Description' => ['required', 'string'],
        ];
    }

}
