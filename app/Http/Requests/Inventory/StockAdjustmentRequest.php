<?php

namespace App\Http\Requests\Inventory;

use App\Models\Inventory\StockItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

// Correctly import Illuminate\Validation\Validator

// Import StockItem model

class StockAdjustmentRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'AdjustmentDate' => ['required', 'date'],
            'Branch' => ['required', 'integer', 'exists:t_Branches,Id'],
            'AdjustedBy' => ['required', 'integer', 'exists:t_Users,Id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.Item' => ['required', 'integer', 'exists:t_Items,Id'],
            'items.*.UOM' => ['required', 'integer', 'exists:t_UOM,Id'],
            'items.*.UnitCost' => ['nullable', 'numeric', 'min:0'],
            'items.*.AdjustmentQty' => ['required', 'numeric'],
            'items.*.Reason' => ['required', 'integer', 'exists:t_CodeDetails,ID'],
            'items.*.Remarks' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param Validator $validator // Changed the type hint here!
     * @return void
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $items = $this->input('items');
            $branchId = $this->input('Branch');

            if (!$items || !is_array($items) || !$branchId) {
                return;
            }

            // Get all unique Item IDs from the incoming request for efficient querying
            $requestedItemIds = collect($items)->pluck('Item')->unique()->toArray();

            // Fetch current quantities for all relevant items in the specified branch
            $currentStocks = StockItem::where('Branch', $branchId)
                ->whereIn('ItemID', $requestedItemIds)
                ->pluck('CurrentQty', 'ItemID');

            foreach ($items as $index => $itemData) {
                $itemId = $itemData['Item'] ?? null;
                $adjustmentQty = $itemData['AdjustmentQty'] ?? null;

                if ($itemId !== null && is_numeric($adjustmentQty)) {
                    // Get the current quantity for this specific item. Default to 0 if not found.
                    // This handles cases where an item might not exist in stock yet.
                    $currentQty = $currentStocks->get($itemId, 0);

                    // Calculate the potential new quantity after adjustment
                    $newQty = $currentQty + (float)$adjustmentQty;

                    // Add an error if the new quantity would be negative
                    if ($newQty < 0) {
                        $validator->errors()->add(
                            "items.{$index}.AdjustmentQty",
                            "The adjusted quantity for item '{$itemId}' (Current: {$currentQty}) would result in a negative stock balance ({$newQty}). Please enter a valid quantity."
                        );
                    }
                }
            }
        });
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'items.*.AdjustmentQty.required' => 'The adjustment quantity is required for each item.',
            'items.*.AdjustmentQty.numeric' => 'The adjustment quantity must be a number.',
            'items.*.Item.exists' => 'The selected item does not exist.',
            'Branch.exists' => 'The selected branch does not exist.',
            'AdjustedBy.exists' => 'The selected user does not exist.',
        ];
    }
}
