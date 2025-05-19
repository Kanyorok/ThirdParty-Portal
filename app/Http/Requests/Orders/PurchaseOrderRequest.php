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

            'supplier'      => ['required'],
            'pODate'  => ['required'],
            'priority'      => ['nullable'],
            'refNo'     => ['nullable'],
            'terms'         => ['nullable'],


            'itemCode'    => 'required|array|min:1',
//            'itemCode.*'  => 'required|integer|exists:items,id',
            'quantity'    => 'required|array',
            'quantity.*'  => 'required|numeric|min:1',
            'unitPrice'   => 'required|array',
            'unitPrice.*' => 'required|numeric|min:0',
            'tax'         => 'nullable|array',
            'tax.*'       => 'nullable|numeric|min:0',
            'discount'    => 'nullable|array',
            'discount.*'  => 'nullable|numeric|min:0',
            'lineTotal'   => 'required|array',
            'lineTotal.*' => 'required|numeric|min:0',

//            'ItemId' =>    ['required','array'],
//            'ItemId.*' =>    ['required','exists:items,id'],

        ];
    }
}
