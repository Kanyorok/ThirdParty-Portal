<?php

namespace App\Http\Requests\DMS;

use App\Models\DMS\DocumentValidationType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class DocumentValidationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ValidationType' => ['required', 'string', 'max:200'],
        ];
    }

    public function getValidationType(): DocumentValidationType
    {
        $ValidationType = DocumentValidationType::query()->where('ValidationTypeId', $this->string('ValidationType'))->first();
        if ($ValidationType instanceof DocumentValidationType) {
            return $ValidationType;
        }
        throw ValidationException::withMessages(['ValidationType' => 'invalid validation type']);
    }

    public function createValidationId(DocumentValidationType $type):string
    {
        $id = $type->validations()->count();
        do{
            $validationId =  $type->ValidationTypeId.'-'.$id;
        }while(DocumentValidationType::query()->where('ValidationTypeId',$validationId)->exists());
        return $validationId;
    }
}
