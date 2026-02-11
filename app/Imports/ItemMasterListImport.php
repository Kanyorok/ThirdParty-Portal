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
        DB::beginTransaction();

        try {
            $this->processed++;
            $rowNumber = $this->processed + 1;
            $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

            if (isset($row['itemcode']) && str_starts_with($row['itemcode'], '---')) {
                DB::rollBack();

                return null;
            }

            if (empty($row['itemcode']) && empty($row['itemname'])) {
                DB::rollBack();

                return null;
            }

            $requiredColumns = [
                'itemcode', 'itemname', 'itemtype', 'uom',
                'inventorytype', 'category',
            ];

            foreach ($requiredColumns as $col) {
                if (! array_key_exists($col, $row)) {
                    throw new \Exception("The uploaded file is missing required column: {$col}");
                }
            }

            $validationErrors = $this->validateRequiredFields($row, $rowNumber);
            if (! empty($validationErrors)) {
                $this->skipped++;
                $this->errors = array_merge($this->errors, $validationErrors);
                DB::rollBack();

                return null;
            }

            $itemCode = $row['itemcode'];
            $itemName = $row['itemname'];
            $itemType = $row['itemtype'];
            $uom = $row['uom'];
            $inventoryType = $row['inventorytype'];
            $category = $row['category'];

            $itemTypeRecord = ItemType::whereHas('type', function ($q) use ($itemType) {
                $q->where('Description', $itemType);
            })->where('Active', 1)->first();

            if (! $itemTypeRecord) {
                $errorMsg = "Row {$rowNumber}: Invalid Item Type '{$itemType}'";
                $this->skipped++;
                $this->errors[] = $errorMsg;
                DB::rollBack();

                return null;
            }
            $itemTypeId = $itemTypeRecord->Id;

            $uomRecord = UnitOfMeasure::where('Code', $uom)
                ->where('Active', 1)
                ->first();
            if (! $uomRecord) {
                $errorMsg = "Row {$rowNumber}: Invalid UOM '{$uom}'";
                $this->skipped++;
                $this->errors[] = $errorMsg;
                DB::rollBack();

                return null;
            }
            $uomId = $uomRecord->Id;

            $inventoryTypeRecord = InventoryType::whereHas('type', function ($q) use ($inventoryType) {
                $q->where('Description', $inventoryType);
            })->where('Status', 1)->first();

            if (! $inventoryTypeRecord) {
                $errorMsg = "Row {$rowNumber}: Invalid Inventory Type '{$inventoryType}'";
                $this->skipped++;
                $this->errors[] = $errorMsg;
                DB::rollBack();

                return null;
            }
            $inventoryTypeId = $inventoryTypeRecord->Id;

            $statusValue = $row['status'] ?? 'Active';
            $statusId = $this->lookupCodeDetailId('ItemStatus', $statusValue);
            if (! $statusId) {
                $statusId = $this->getDefaultStatusId();
            }

            $priceId = $this->lookupOrCreatePrice($row['itemprice'] ?? null);

            $categoryName = $category;
            $parentCategoryName = $row['parentcategory'] ?? null;

            $categoryId = $this->findCategoryId($categoryName, $parentCategoryName);
            if (! $categoryId) {
                $parentText = $parentCategoryName ? " with parent '{$parentCategoryName}'" : "";
                $errorMsg = "Row {$rowNumber}: Category '{$categoryName}'{$parentText} not found";
                $this->skipped++;
                $this->errors[] = $errorMsg;
                DB::rollBack();

                return null;
            }

            $existingItem = ItemMasterList::where('ItemCode', $itemCode)->first();

            if ($existingItem) {
                if ($existingItem->inUse()) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNumber}: Item '{$itemCode}' is currently in use and cannot be updated via import";
                    DB::rollBack();

                    return null;
                }

                $existingItem->BarCode = $row['barcode'] ?? $existingItem->BarCode;
                $existingItem->ItemName = $itemName;
                $existingItem->ItemType = $itemTypeId;
                $existingItem->UOM = $uomId;
                $existingItem->InventoryType = $inventoryTypeId;
                $existingItem->Category = $categoryId;
                $existingItem->Status = $statusId;
                $existingItem->ItemDescription = $row['itemdescription'] ?? $existingItem->ItemDescription;

                if (! empty($row['itemprice']) && $priceId !== null) {
                    $existingItem->ItemPrice = $priceId;
                }

                $existingItem->ModifiedBy = Auth::id();
                $existingItem->ModifiedOn = now();

                $existingItem->save();
                $this->updated++;

                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($existingItem)
                    ->event('updated')
                    ->log('Item updated via Excel import');

                DB::commit();

                return null;
            }

            $newItemData = [
                'ItemCode' => $itemCode,
                'BarCode' => $row['barcode'] ?? null,
                'ItemName' => $itemName,
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

            if (! empty($row['itemprice']) && $priceId !== null) {
                $newItemData['ItemPrice'] = $priceId;
            }

            $newItem = new ItemMasterList($newItemData);
            $newItem->save();

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
            $rowNumber = $this->processed + 1;
            $errorMsg = "Row {$rowNumber}: " . $e->getMessage();
            $this->errors[] = $errorMsg;

            return null;
        }
    }

    private function validateRequiredFields(array $row, int $rowNumber): array
    {
        $errors = [];

        $requiredFields = [
            'itemcode' => 'Item Code',
            'itemname' => 'Item Name',
            'itemtype' => 'Item Type',
            'uom' => 'UOM',
            'inventorytype' => 'Inventory Type',
            'category' => 'Category',
        ];

        foreach ($requiredFields as $field => $label) {
            $value = $row[$field] ?? null;
            if (empty($value) || trim($value) === '') {
                $errors[] = "Row {$rowNumber}: {$label} is required";
            }
        }

        return $errors;
    }

    private function lookupCodeDetailId(string $codeId, ?string $description)
    {
        if (empty($description) || strtoupper($description) === 'N/A' || $description === '-') {
            return null;
        }

        $codeDetail = CodeDetail::where('CodeID', $codeId)
            ->where('Description', $description)
            ->first();

        return $codeDetail ? $codeDetail->ID : null;
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

        $price = PriceManagement::where('ActualPrice', $numericValue)->first();

        if (! $price) {
            $price = PriceManagement::create([
                'ActualPrice' => $numericValue,
                'Description' => 'Imported price',
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ]);
        }

        return $price->Id;
    }

    private function findCategoryId(?string $categoryName, ?string $parentCategoryName = null)
    {
        if (empty($categoryName) || $categoryName === '-' || $categoryName === '') {
            return null;
        }

        $query = ItemCategories::where('Name', $categoryName)
            ->whereHas('status', fn ($q) => $q->where('Description', 'Active'));

        if (! empty($parentCategoryName) && $parentCategoryName !== '-' && $parentCategoryName !== '') {
            $query->whereHas('parent', function ($q) use ($parentCategoryName) {
                $q->where('Name', $parentCategoryName);
            });
        } else {
            $query->whereNull('ParentId');
        }

        $category = $query->first();

        return $category ? $category->Id : null;
    }

    private function getDefaultStatusId()
    {
        $status = CodeDetail::where('CodeID', 'ItemStatus')
            ->where('Description', 'Active')
            ->first();

        return $status ? $status->ID : null;
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
