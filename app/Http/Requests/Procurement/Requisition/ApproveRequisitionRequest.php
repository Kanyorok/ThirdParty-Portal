<?php

namespace App\Http\Requests\Procurement\Requisition;

use Illuminate\Foundation\Http\FormRequest;

class ApproveRequisitionRequest extends FormRequest
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
            'requisition_id' => ['required', 'integer'],
            'document_type' => ['required', 'string'],
            'action' => ['required', 'string'],
            'requisition_total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'requisition_id.required' => 'The requisition ID is required.',
            'document_type.in' => 'Invalid document type.',
            'requisition_total.numeric' => 'Requisition total must be a valid number.',
        ];
    }
}
