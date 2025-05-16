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

            'Supplier'      => ['required'],
            'SupplierDate'  => ['required'],
            'Priority'      => ['nullable'],
            'RFQNumber'     => ['nullable'],
            'Terms'         => ['nullable'],

//            'ItemId' =>    ['required','array'],
//            'ItemId.*' =>    ['required','exists:items,id'],

        ];
    }
}
