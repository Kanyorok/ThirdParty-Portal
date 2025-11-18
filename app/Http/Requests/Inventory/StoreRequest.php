<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
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
        $storeId = $this->route('Id'); // Get the store ID if it's an update

        return [
            'StoreName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_Stores', 'StoreName')
                    ->ignore($storeId)
                    ->whereNull('DeletedOn'), // ← exclude soft-deleted stores
            ],
            'BranchID' => 'required|integer|exists:t_Branches,Id',
            'Status' => 'required|boolean',
            'IsMainStore' => [
                'nullable',
                'boolean',
                // Custom validation to ensure only one main store per branch
                function ($attribute, $value, $fail) use ($storeId) {
                    if ($value === true) {
                        $branchId = $this->BranchID ?? null;
                        if (! $branchId) {
                            $fail('Branch must be specified when marking a store as main.');
                            return;
                        }

                        $existingMainStore = \App\Models\Inventory\Store::where('BranchID', $branchId)
                            ->where('IsMainStore', true)
                            ->whereNull('DeletedOn')
                            ->when($storeId, function ($query) use ($storeId) {
                                return $query->where('Id', '!=', $storeId);
                            })
                            ->exists();

                        if ($existingMainStore) {
                            $fail('A main store already exists for this branch. Only one main store is allowed per branch.');
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'StoreName.unique' => 'The Store Name already exists.',
            'IsMainStore.custom' => 'A main store already exists for this branch.',
        ];
    }
}