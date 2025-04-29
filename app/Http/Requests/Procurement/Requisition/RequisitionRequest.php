<?php

namespace App\Http\Requests\Procurement\Requisition;

use Illuminate\Foundation\Http\FormRequest;

class RequisitionRequest extends FormRequest
{


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'Category' => ['required'],
            'Branch' => ['required'],
            'Department' =>   ['required'],
            'Remarks' =>    ['nullable'],
//            'Quantity' =>   ['required'],
//            'Urgency' =>    ['required'],
//            'UOM' =>    ['nullable'],
//            'ExpectedPrice' =>  ['nullable'],
//            'ActualPrice' =>    ['nullable'],
//            'CategoryId' =>    ['nullable'],

            //
               ];
    }
}
