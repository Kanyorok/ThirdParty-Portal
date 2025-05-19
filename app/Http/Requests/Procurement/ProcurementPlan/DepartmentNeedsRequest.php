<?php

namespace App\Http\Requests\Procurement\ProcurementPlan;

use Illuminate\Foundation\Http\FormRequest;

class DepartmentNeedsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'NeedID' => 'required',
            'ItemID' => 'required|exists:t_Items,Id',
            'RequestedQty' => 'required|integer|min:1',
            'EstimatedUnitCost' => 'required|numeric|min:0',
            'Justification' => 'nullable|string',
            'Status' => 'required|string',
            'FiscalYear' => 'required|integer',
            'PriorityLevel' => 'nullable|string',
            'IsEmergency' => 'nullable|boolean',
            'RequestedDate' => 'required|date',
            'BranchID' => 'required|exists:t_Branches,Id',
            'DepartmentID' => 'required|exists:t_Departments,Id',
        ];

    }
}
