<?php

namespace App\Imports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\ItemType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemMasterListImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        try {
            // ✅ Normalize and trim all inputs
            $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);

            // ✅ Support both lowercase & uppercase Excel headings
            $itemCode       = $row['itemcode'] ?? $row['ItemCode'] ?? null;
            $barCode        = $row['barcode'] ?? $row['BarCode'] ?? null;
            $itemName       = $row['itemname'] ?? $row['ItemName'] ?? null;
            $itemTypeName   = $row['itemtype'] ?? $row['ItemType'] ?? null;
            $uomCode        = $row['uom'] ?? $row['UOM'] ?? null;
            $inventoryType  = $row['inventorytype'] ?? $row['InventoryType'] ?? null;
            $categoryName   = $row['category'] ?? $row['Category'] ?? null;
            $parentCategory = $row['parentcategory'] ?? $row['ParentCategory'] ?? null;

            if (empty($itemName)) {
                Log::warning("❌ Skipped row: missing ItemName", $row);
                return null;
            }

            // ✅ Resolve related lookups
            $itemTypeModel = ItemType::where('TypeName', $itemTypeName)->first();
            $uomModel = UnitOfMeasure::where('Code', $uomCode)->first();
            $inventoryTypeModel = InventoryType::where('Type', $inventoryType)->first();

            // Handle Category or Subcategory
            $categoryModel = null;
            if ($categoryName) {
                $query = ItemCategories::where('Name', $categoryName);
                if ($parentCategory) {
                    $parent = ItemCategories::where('Name', $parentCategory)->first();
                    if ($parent) {
                        $query->where('ParentId', $parent->Id);
                    }
                }
                $categoryModel = $query->first();
            }

            // ✅ Check if item already exists
            $existingItem = ItemMasterList::query()
                ->when($itemCode, fn($q) => $q->where('ItemCode', $itemCode))
                ->when(!$itemCode, fn($q) => $q->where('ItemName', $itemName))
                ->first();

            // ✅ Prepare data
            $data = [
                'BarCode'        => $barCode,
                'ItemName'       => $itemName,
                'ItemType'       => $itemTypeModel?->Id,
                'UOM'            => $uomModel?->Id,
                'InventoryType'  => $inventoryTypeModel?->Id,
                'Category'       => $categoryModel?->Id,
                'ModifiedBy'     => Auth::id(),
                'ModifiedOn'     => now(),
            ];

            if ($existingItem) {
                // ✅ Update existing item only if changed
                $hasChanges = false;
                foreach ($data as $key => $value) {
                    if (($existingItem->$key ?? null) != ($value ?? null)) {
                        $hasChanges = true;
                        break;
                    }
                }

                if ($hasChanges) {
                    $existingItem->update($data);
                    Log::info("🔁 Updated existing item: {$existingItem->ItemCode}");
                } else {
                    Log::info("⏭ Skipped identical item: {$existingItem->ItemCode}");
                }

                return null; // skip creating duplicate
            }

            // ✅ Create a new item if none exists
            return new ItemMasterList([
                'ItemCode'       => $itemCode ?: $this->generateItemCode(),
                'BarCode'        => $barCode,
                'ItemName'       => $itemName,
                'ItemType'       => $itemTypeModel?->Id,
                'UOM'            => $uomModel?->Id,
                'InventoryType'  => $inventoryTypeModel?->Id,
                'Category'       => $categoryModel?->Id,
                'CreatedBy'      => Auth::id(),
                'ModifiedBy'     => Auth::id(),
                'CreatedOn'      => now(),
                'ModifiedOn'     => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Import failed: " . $e->getMessage(), ['row' => $row]);
            return null;
        }
    }

    /**
     * ✅ Generate a unique ItemCode when missing.
     */
    protected function generateItemCode(): string
    {
        $nextId = (ItemMasterList::max('Id') ?? 0) + 1;
        return 'ITM-' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }
}
