<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class OpeningStockRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
        'BranchId' => 'required|exists:t_Branches,Id',
        'StoreId'   => 'required|exists:t_Stores,Id',
        'ItemCode'  => 'required|exists:t_Items,ItemCode',
        'Date'  => 'required|date',
        'Quantity'  =>'required|integer',
        'UOM'   =>'required|exists:t_Items,UOM',
        'Value' => 'required|decimal',
        'Remarks'   => 'string', 
        ];
    }
}
