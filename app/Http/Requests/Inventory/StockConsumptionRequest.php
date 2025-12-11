<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Auth\User;
use App\Models\HRM\Department;
use App\Models\HRM\Employee;
use App\Models\Core\Approval\CodeDetail;


class StockConsumptionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'BranchID' => 'required|exists:t_Branches,Id',
            'StoreID' => 'required|exists:t_Stores,Id',
            'ItemID' => 'required|exists:t_StockItems,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'Quantity' => 'required|numeric|min:0.01',
            'IssuedToType' => 'required|exists:t_CodeDetails,ID',
            'IssuedToID' => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = $this->input('IssuedToType');
                    $typeDetail = CodeDetail::find($type);
                    
                    if (!$typeDetail) {
                        $fail('Invalid issued to type.');
                        return;
                    }
                    
                    $typeName = strtoupper($typeDetail->Description);
                    
                    if ($typeName === 'EMPLOYEE') {
                        // Check if it's a valid user ID
                        if (!User::where('Id', $value)->exists()) {
                            $fail('The selected issued to employee is invalid.');
                        }
                    } elseif ($typeName === 'DEPARTMENT') {
                        // Check if it's a valid department ID
                        if (!Department::where('Id', $value)->exists()) {
                            $fail('The selected issued to department is invalid.');
                        }
                    } else {
                        $fail('Invalid issued to type.');
                    }
                }
            ],
            'IssuedBy' => 'required|exists:t_Users,Id',
            'IssuedOn' => 'required|date',
            'Remarks' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'BranchID.required' => 'Branch is required.',
            'StoreID.required' => 'Store is required.',
            'ItemID.required' => 'Item is required.',
            'UOM.required' => 'Unit of measure is required.',
            'Quantity.required' => 'Quantity is required.',
            'Quantity.min' => 'Quantity must be at least 0.01.',
            'IssuedToType.required' => 'Issued to type is required.',
            'IssuedToID.required' => 'Issued to is required.',
            'IssuedBy.required' => 'Issued by is required.',
            'IssuedOn.required' => 'Issued on date is required.',
            'IssuedOn.date' => 'Issued on must be a valid date.',
        ];
    }

    public function attributes()
    {
        return [
            'BranchID' => 'branch',
            'StoreID' => 'store',
            'ItemID' => 'item',
            'UOM' => 'unit of measure',
            'Quantity' => 'quantity',
            'IssuedToType' => 'issued to type',
            'IssuedToID' => 'issued to',
            'IssuedBy' => 'issued by',
            'IssuedOn' => 'issued on',
            'Remarks' => 'remarks',
        ];
    }
}