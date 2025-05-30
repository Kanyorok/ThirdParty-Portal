<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Inventory\ItemCategories;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class InterBranchRequisitionRequest extends FormRequest
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
            'FromBranch' => 'required|exists:t_Branches,Id',
            'ToBranch' => 'required|exists:t_Branches,Id|different:FromBranch',
            'ItemCode' => 'required|exists:t_Items,Id',
            'UOM' => 'required|exists:t_UOM,Id',
            'RequestedQty' => 'required|integer|min:1',
            'Remarks' => 'nullable|string|max:255',
        ];
    }
}
