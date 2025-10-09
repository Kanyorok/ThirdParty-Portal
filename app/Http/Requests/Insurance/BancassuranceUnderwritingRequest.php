<?php

namespace App\Http\Requests\Insurance;

use Illuminate\Foundation\Http\FormRequest;

class BancassuranceUnderwritingRequest extends FormRequest
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
            'FeedbackDate' => 'required|date',
            'RiskScore' => 'required|integer',
            'Decision' => 'required|exists:t_CodeDetails,ID',
            'Comments' => 'nullable|string',
        ];
    }
}
