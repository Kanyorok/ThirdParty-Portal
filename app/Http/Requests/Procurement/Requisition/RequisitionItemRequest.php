<?php

namespace App\Http\Requests\Procurement\Requisition;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionItemRequest extends FormRequest
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

            'RequisitionID' => ['nullable', 'integer'],
            'Type' => ['required', 'string'],
            'Item' => ['required', 'integer'],
            // 'NeededBy' => ['nullable', 'date'],
            'Quantity' => ['required', 'numeric'],
            'Urgency' => ['required', 'integer'],
            'UOM' => ['nullable', 'string'],
            // 'ExpectedPrice' => ['nullable', 'numeric'],
            // 'ActualPrice' => ['nullable', 'numeric'],
            'CategoryId' => ['nullable', 'integer'],
        ];
    }

}
