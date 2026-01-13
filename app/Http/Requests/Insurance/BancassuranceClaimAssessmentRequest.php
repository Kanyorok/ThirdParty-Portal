<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceClaimAssessmentRequest extends FormRequest
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
            'AssessmentComments' => 'required|string|max:1000',
            'Decision' => 'required|exists:t_CodeDetails,ID',
            'AssessmentAmount' => 'required|numeric|regex:/^\d+(\.\d{1,2})?$/',
            'file' => 'nullable|array',
            'file.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png,docx,xlsx|max:25600'
        ];
    }
}
