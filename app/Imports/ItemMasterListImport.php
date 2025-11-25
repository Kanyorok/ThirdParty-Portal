<?php

namespace App\Imports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\UnitOfMeasure;
use App\Models\Inventory\ItemType;
use App\Models\Core\Approval\CodeDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemMasterListImport implements ToModel, WithHeadingRow
{
    private $processed = 0;
    private $created = 0;
    private $updated = 0;
    private $skipped = 0;

    public function model(array $row)
    {
        DB::beginTransaction();
        try {
            $this->processed++;
            
            $row = array_map(fn($v) => is_string($v) ? trim($v) : $v, $row);

            $itemCode = $row['itemcode'] ?? $row['ItemCode'] ?? null;
            $itemName = $row['itemname'] ?? $row['ItemName'] ?? null;

            if (empty($itemCode) || empty($itemName)) {
                $this->skipped++;
                Log::warning("❌ Skipped row: missing ItemCode or ItemName", $row);
                DB::rollBack();
                return null;
            }

            // Lookup foreign keys
            $itemType = ItemType::where('TypeName', $row['itemtype'] ?? '')->first();
            $uom = UnitOfMeasure::where('Code', $row['uom'] ?? '')->first();
            $inventoryType = InventoryType::where('Type', $row['inventorytype'] ?? '')->first();
            
            // Handle category hierarchy - look for both category and parent category
            $categoryName = $row['category'] ?? null;
            $parentCategoryName = $row['parentcategory'] ?? null;
            
            $category = $this->findCategory($categoryName, $parentCategoryName);

            // Check if item already exists
            $existingItem = ItemMasterList::where('ItemCode', $itemCode)->first();

            if ($existingItem) {
                // Update existing item
                $updateData = [
                    'BarCode'        => $row['barcode'] ?? $existingItem->BarCode,
                    'ItemName'       => $itemName,
                    'ItemType'       => $itemType?->Id ?? $existingItem->ItemType,
                    'UOM'            => $uom?->Id ?? $existingItem->UOM,
                    'InventoryType'  => $inventoryType?->Id ?? $existingItem->InventoryType,
                    'Category'       => $category?->Id ?? $existingItem->Category,
                    'ModifiedBy'     => Auth::id(),
                    'ModifiedOn'     => now(),
                ];

                $hasChanges = false;
                foreach ($updateData as $key => $value) {
                    if ($existingItem->$key != $value) {
                        $hasChanges = true;
                        break;
                    }
                }

                if ($hasChanges) {
                    $existingItem->update($updateData);
                    Log::info("🔁 Updated existing item: {$itemCode}");
                    $this->updated++;
                } else {
                    Log::info("⏭ Skipped identical item: {$itemCode}");
                    $this->skipped++;
                }

                DB::commit();
                return null;
            }

            // Create new item - handle ItemCode generation properly
            $newItemData = [
                'ItemCode'       => $itemCode, 
                'BarCode'        => $row['barcode'] ?? null,
                'ItemName'       => $itemName,
                'ItemType'       => $itemType?->Id,
                'UOM'            => $uom?->Id,
                'InventoryType'  => $inventoryType?->Id,
                'Category'       => $category?->Id,
                'Status'         => $this->getDefaultStatus(),
                'CreatedBy'      => Auth::id(),
                'ModifiedBy'     => Auth::id(),
                'CreatedOn'      => now(),
                'ModifiedOn'     => now(),
            ];

            Log::info("🎯 Creating new item", $newItemData);

            // Create the item
            $newItem = new ItemMasterList($newItemData);
            $newItem->save();

            if (empty($itemCode)) {
                $newItem->ItemCode = 'ITM-' . str_pad($newItem->Id, 5, '0', STR_PAD_LEFT);
                $newItem->save();
            }

            $this->created++;
            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($newItem)
                ->event('imported')
                ->log('Item imported via Excel');

            return $newItem;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->skipped++;
            Log::error("❌ Import failed for row {$this->processed}: " . $e->getMessage(), [
                'row' => $row,
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Find category considering hierarchy
     */
    private function findCategory(?string $categoryName, ?string $parentCategoryName = null)
    {
        if (empty($categoryName)) {
            return null;
        }

        $query = ItemCategories::where('Name', $categoryName);

        if (!empty($parentCategoryName)) {
            // Look for subcategory with specified parent
            $query->whereHas('parent', function($q) use ($parentCategoryName) {
                $q->where('Name', $parentCategoryName);
            });
        } else {
            // Look for main category (no parent)
            $query->whereNull('ParentId');
        }

        return $query->first();
    }

    /**
     * Get default active status
     */
    private function getDefaultStatus()
    {
        return CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('Id');
    }

    public function getProcessedCount(): int { return $this->processed; }
    public function getCreatedCount(): int { return $this->skipped; }
    public function getUpdatedCount(): int { return $this->updated; }
    public function getSkippedCount(): int { return $this->skipped; }
}