<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class WorkFlowLimitRequest extends FormRequest
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
            'WorkFlowStageId' => 'required|exists:t_WorkFlowStages,Id',
            'Permission' => 'required|integer',
            'AmountLimit' => ['required', 'array', 'min:1'],
        'AmountLimit.*' => ['required', 'numeric', 'min:0', 'distinct'],
        ]; 
    }
}
