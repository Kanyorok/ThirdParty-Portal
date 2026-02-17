<?php

namespace App\Imports;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\InventoryType;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\ItemType;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ItemMasterListImport implements ToModel, WithHeadingRow
{
    private $processed = 0;
    private $created = 0;
    private $updated = 0;
    private $skipped = 0;
    private $errors = [];

    public function model(array $row)
    {
        $this->processed++;
        $rowNumber = $this->processed + 1;

        $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

        $itemCode = $row['itemcode'] ?? '';

        if (empty($itemCode) && empty($row['itemname'] ?? '')) {
            $this->processed--;

            return null;
        }

        if (
            str_starts_with($itemCode, '---') ||
            str_starts_with($itemCode, '===') ||
            str_starts_with($itemCode, '#') ||
            stripos($itemCode, 'reference') !== false ||
            stripos($itemCode, 'example') !== false ||
            stripos($itemCode, 'sample') !== false ||
            stripos($itemCode, 'template') !== false
        ) {
            $this->processed--;
            $this->skipped++;

            return null;
        }

        $requiredColumns = ['itemcode', 'itemname', 'itemtype', 'uom', 'inventorytype', 'category'];
        foreach ($requiredColumns as $col) {
            if (! array_key_exists($col, $row)) {
                $this->errors[] = "File format error: Missing column '{$col}'. Please use the template.";
                $this->skipped++;

                return null;
            }
        }

        if (! $this->hasRequiredFields($row)) {
            $this->skipped++;

            return null;
        }

        $itemTypeId = $this->findItemTypeId($row['itemtype']);
        if (! $itemTypeId) {
            $this->skipped++;

            return null;
        }

        $uomId = $this->findUomId($row['uom']);
        if (! $uomId) {
            $this->skipped++;

            return null;
        }

        $inventoryTypeId = $this->findInventoryTypeId($row['inventorytype']);
        if (! $inventoryTypeId) {
            $this->skipped++;

            return null;
        }

        $categoryId = $this->findCategoryId($row['category'], $row['parentcategory'] ?? null);
        if (! $categoryId) {
            $this->skipped++;

            return null;
        }

        $statusId = $this->lookupCodeDetailId('ItemStatus', $row['status'] ?? 'Active')
                    ?? $this->getDefaultStatusId();

        $priceId = $this->lookupOrCreatePrice($row['itemprice'] ?? null);

        $existingItem = ItemMasterList::with('status')->where('ItemCode', $itemCode)->first();

        if ($existingItem) {
            if ($existingItem->inUse()) {
                $this->skipped++;

                return null;
            }

            $isInactive = $existingItem->status && strtolower($existingItem->status->Description) === 'inactive';
            if ($isInactive) {
                $this->skipped++;

                return null;
            }

            if ($this->isUnchanged($existingItem, $row, $itemTypeId, $uomId, $inventoryTypeId, $categoryId, $statusId, $priceId)) {
                $this->processed--;

                return null;
            }

            DB::beginTransaction();

            try {
                $existingItem->BarCode = $row['barcode'] ?? $existingItem->BarCode;
                $existingItem->ItemName = $row['itemname'];
                $existingItem->ItemType = $itemTypeId;
                $existingItem->UOM = $uomId;
                $existingItem->InventoryType = $inventoryTypeId;
                $existingItem->Category = $categoryId;
                $existingItem->Status = $statusId;
                $existingItem->ItemDescription = $row['itemdescription'] ?? $existingItem->ItemDescription;
                if ($priceId) {
                    $existingItem->ItemPrice = $priceId;
                }
                $existingItem->ModifiedBy = Auth::id();
                $existingItem->ModifiedOn = now();
                $existingItem->save();

                $this->updated++;
                DB::commit();

                return null;

            } catch (\Throwable $e) {
                DB::rollBack();
                $this->skipped++;

                $message = $e->getMessage();
                if (config('app.debug')) {
                    $this->errors[] = "Row {$rowNumber}: Update failed - {$message}";
                } else {
                    $this->errors[] = "Row {$rowNumber}: System error during update";
                }

                return null;
            }
        }

        DB::beginTransaction();

        try {
            $newItemData = [
                'ItemCode' => $itemCode,
                'BarCode' => $row['barcode'] ?? null,
                'ItemName' => $row['itemname'],
                'ItemType' => $itemTypeId,
                'UOM' => $uomId,
                'InventoryType' => $inventoryTypeId,
                'Category' => $categoryId,
                'Status' => $statusId,
                'ItemDescription' => $row['itemdescription'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ];
            if ($priceId) {
                $newItemData['ItemPrice'] = $priceId;
            }

            $newItem = new ItemMasterList($newItemData);
            $newItem->save();

            $this->created++;
            DB::commit();

            return $newItem;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->skipped++;

            $message = $e->getMessage();

            if (str_contains($message, 'Duplicate entry')) {
                $this->errors[] = "Row {$rowNumber}: Duplicate item code '{$itemCode}'";
            } elseif (str_contains($message, 'foreign key constraint')) {
                $this->errors[] = "Row {$rowNumber}: Invalid reference data";
            } elseif (config('app.debug')) {
                $this->errors[] = "Row {$rowNumber}: Creation failed - {$message}";
            } else {
                $this->errors[] = "Row {$rowNumber}: System error during creation";
            }

            return null;
        }
    }

    private function hasRequiredFields(array $row): bool
    {
        $requiredFields = ['itemcode', 'itemname', 'itemtype', 'uom', 'inventorytype', 'category'];

        foreach ($requiredFields as $field) {
            $value = $row[$field] ?? null;
            if (empty($value) || trim($value) === '' || trim($value) === '-') {
                return false;
            }
        }

        return true;
    }

    private function isUnchanged($item, array $row, $itemTypeId, $uomId, $inventoryTypeId, $categoryId, $statusId, $priceId): bool
    {
        $current = [
            'ItemName' => $item->ItemName,
            'BarCode' => $item->BarCode ?? '',
            'ItemType' => $item->ItemType,
            'UOM' => $item->UOM,
            'InventoryType' => $item->InventoryType,
            'Category' => $item->Category,
            'Status' => $item->Status,
            'ItemDescription' => $item->ItemDescription ?? '',
            'ItemPrice' => $item->ItemPrice,
        ];

        $incoming = [
            'ItemName' => $row['itemname'],
            'BarCode' => $row['barcode'] ?? '',
            'ItemType' => $itemTypeId,
            'UOM' => $uomId,
            'InventoryType' => $inventoryTypeId,
            'Category' => $categoryId,
            'Status' => $statusId,
            'ItemDescription' => $row['itemdescription'] ?? '',
            'ItemPrice' => $priceId,
        ];

        return $current == $incoming;
    }

    private function findItemTypeId($itemType)
    {
        if (empty($itemType) || trim($itemType) === '-') {
            return null;
        }

        $record = ItemType::whereHas('type', fn ($q) => $q->where('Description', $itemType))
            ->where('Active', 1)
            ->first();

        return $record ? $record->Id : null;
    }

    private function findUomId($uomCode)
    {
        if (empty($uomCode) || trim($uomCode) === '-') {
            return null;
        }

        $record = UnitOfMeasure::where('Code', $uomCode)
            ->where('Active', 1)
            ->first();

        return $record ? $record->Id : null;
    }

    private function findInventoryTypeId($inventoryType)
    {
        if (empty($inventoryType) || trim($inventoryType) === '-') {
            return null;
        }

        $record = InventoryType::whereHas('type', fn ($q) => $q->where('Description', $inventoryType))
            ->where('Status', 1)
            ->first();

        return $record ? $record->Id : null;
    }

    private function findCategoryId($categoryName, $parentCategoryName)
    {
        if (empty($categoryName) || $categoryName === '-') {
            return null;
        }

        $query = ItemCategories::where('Name', $categoryName)
            ->whereHas('status', fn ($q) => $q->where('Description', 'Active'));

        if (! empty($parentCategoryName) && $parentCategoryName !== '-' && $parentCategoryName !== '') {
            $query->whereHas('parent', fn ($q) => $q->where('Name', $parentCategoryName));
        } else {
            $query->whereNull('ParentId');
        }

        $category = $query->first();

        return $category ? $category->Id : null;
    }

    private function lookupCodeDetailId(string $codeId, ?string $description)
    {
        if (empty($description) || in_array(strtoupper($description), ['N/A', '-'])) {
            return null;
        }

        return CodeDetail::where('CodeID', $codeId)
            ->where('Description', $description)
            ->value('ID');
    }

    private function lookupOrCreatePrice($priceValue)
    {
        if (empty($priceValue)) {
            return null;
        }

        $priceValue = trim($priceValue);
        $priceValue = preg_replace('/[^\d.]/', '', $priceValue);
        $numericValue = floatval($priceValue);

        if ($numericValue <= 0) {
            return null;
        }

        $price = PriceManagement::firstOrCreate(
            ['ActualPrice' => $numericValue],
            [
                'Description' => 'Imported price',
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]
        );

        return $price->Id;
    }

    private function getDefaultStatusId()
    {
        return CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->value('ID');
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
    }

    public function getCreatedCount(): int
    {
        return $this->created;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return ! empty($this->errors);
    }
}
