<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
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
            'supplier' => ['required'],
            'pODate' => ['required', 'date', 'before_or_equal:today'],
            'priority' => ['nullable'],
            'refNo' => ['nullable'],
            'terms' => ['required', 'exists:t_CodeDetails,ID,CodeID,PaymentTerm'],

            // For unified origination forms
            'origination_type' => ['nullable', 'in:rfq,award,contract,direct'],
            'award_id' => ['nullable', 'integer'],
            'contract_id' => ['nullable', 'integer'],
            'plan_item_id' => ['nullable', 'integer'],
            'rfq_id' => ['nullable'],

            // Item arrays - required for most cases but flexible for unified forms
            'itemCode' => 'required|array|min:1',
            // 'itemCode.*'  => 'required|integer|exists:items,id',

            'quantity' => 'required|array',
            'quantity.*' => ['required', 'numeric', 'min:1'],

            'unitPrice' => 'required|array',
            'unitPrice.*' => ['required', 'numeric', 'min:0'],

            'tax' => 'nullable|array',
            'tax.*' => ['nullable', 'numeric', 'min:0'],

            'discount' => 'nullable|array',
            'discount.*' => ['nullable', 'numeric', 'min:0'],

            'lineTotal' => 'required|array',
            'lineTotal.*' => ['required', 'numeric', 'min:0'],

            // Additional fields for unified form
            'notes' => ['nullable', 'string'],
            'delivery_terms' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
        ];
    }
}
