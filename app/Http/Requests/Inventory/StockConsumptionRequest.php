<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class StockConsumptionRequest extends FormRequest
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
  // app/Http/Requests/Inventory/StockConsumptionRequest.php

// ...
public function rules(): array
{
    $issuedToType = $this->input('IssuedToType');
    $codeDetail = \App\Models\Core\CodeDetail::find($issuedToType);
    $typeDescription = strtoupper($codeDetail?->Description ?? '');
    $issuedToIDRules = ['required', 'integer'];

    if ($typeDescription === 'EMPLOYEE') {
        $issuedToIDRules[] = 'exists:t_Employees,Id';
    } elseif ($typeDescription === 'DEPARTMENT') {
        $issuedToIDRules[] = 'exists:t_Departments,Id';
    }

    return [
        'ItemID' => ['required', 'integer', 'exists:t_StockItems,Id'],
        'UOM' => ['required', 'integer', 'exists:t_UOM,Id'],
        'Quantity' => ['required', 'numeric', 'min:0'],
        'SKUID' => ['nullable', 'integer', 'exists:t_StockItems,Id'],
        'StoreID' => ['nullable', 'integer', 'exists:t_Stores,Id'], 
        'BranchID' => ['required', 'integer', 'exists:t_Branches,Id'],
        'IssuedToType' => ['required', 'integer', 'exists:t_CodeDetails,ID'],
        'IssuedToID' => $issuedToIDRules, 
        'IssuedBy' => ['required', 'integer', 'exists:t_Users,Id'],
        'IssuedOn' => ['required', 'date'],
        'Remarks' => ['nullable', 'string', 'max:255'],
    ];
}
// ...
           
}
