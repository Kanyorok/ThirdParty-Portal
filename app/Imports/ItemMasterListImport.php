<?php

namespace App\Imports;

use App\Models\Core\Approval\CodeDetail;
use App\Models\Inventory\ItemCategories;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ItemMasterListImport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Items' => new ItemsSheetImport(),
            // 'ReferenceData' sheet will be ignored/not processed
        ];
    }
}

class ItemsSheetImport implements ToModel, WithHeadingRow
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
            $rowNumber = $this->processed + 1; // +1 for header row

            $row = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $row);

            // Validate required fields
            $validationErrors = $this->validateRequiredFields($row, $rowNumber);
            if (! empty($validationErrors)) {
                $this->skipped++;
                $this->errors = array_merge($this->errors, $validationErrors);

                DB::rollBack();

                return null;
            }

            $itemCode = $row['itemcode'] ?? $row['ItemCode'] ?? null;
            $itemName = $row['itemname'] ?? $row['ItemName'] ?? null;
            $itemType = $row['itemtype'] ?? null;
            $uom = $row['uom'] ?? null;
            $inventoryType = $row['inventorytype'] ?? null;
            $category = $row['category'] ?? null;

            // Lookup ItemType from CodeDetails using Description
            $itemTypeId = $this->lookupCodeDetailId('ItemTypeStatus', $itemType);
            if (! $itemTypeId) {
                $errorMsg = "Row {$rowNumber}: Invalid Item Type '{$itemType}'";
                $this->skipped++;
                $this->errors[] = $errorMsg;

                DB::rollBack();

                return null;
            }

            // Lookup UOM from UnitOfMeasure using Code
            $uomRecord = \App\Models\Inventory\UnitOfMeasure::where('Code', $uom)->first();
            if (! $uomRecord) {
                $errorMsg = "Row {$rowNumber}: Invalid UOM '{$uom}'";
                $this->skipped++;
                $this->errors[] = $errorMsg;

                DB::rollBack();

                return null;
            }
            $uomId = $uomRecord->Id;

            // Lookup InventoryType from CodeDetails using Description
            $inventoryTypeId = $this->lookupCodeDetailId('InventoryTypeStatus', $inventoryType);
            if (! $inventoryTypeId) {
                $errorMsg = "Row {$rowNumber}: Invalid Inventory Type '{$inventoryType}'";
                $this->skipped++;
                $this->errors[] = $errorMsg;

                DB::rollBack();

                return null;
            }

            // Lookup Status from CodeDetails using Description
            $statusId = $this->lookupCodeDetailId('ItemStatus', $row['status'] ?? 'Active');

            // Handle Price (optional)
            $priceId = $this->lookupOrCreatePrice($row['itemprice'] ?? null);

            // Handle category hierarchy
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

            // Check if item already exists
            $existingItem = ItemMasterList::where('ItemCode', $itemCode)->first();

            if ($existingItem) {
                // Update existing item
                $updateData = [
                    'BarCode' => $row['barcode'] ?? $existingItem->BarCode,
                    'ItemName' => $itemName,
                    'ItemType' => $itemTypeId,
                    'UOM' => $uomId,
                    'InventoryType' => $inventoryTypeId,
                    'Category' => $categoryId,
                    'Status' => $statusId ?? $existingItem->Status,
                    'ItemDescription' => $row['itemdescription'] ?? $existingItem->ItemDescription,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ];

                // Only update ItemPrice if provided
                if (! empty($row['itemprice']) && $priceId !== null) {
                    $updateData['ItemPrice'] = $priceId;
                } else {
                    $updateData['ItemPrice'] = $existingItem->ItemPrice;
                }

                $hasChanges = false;
                foreach ($updateData as $key => $value) {
                    $existingValue = $existingItem->$key;

                    if (
                        ($existingValue === null && $value !== null) ||
                        ($existingValue !== null && $value === null) ||
                        ($existingValue != $value)
                    ) {
                        $hasChanges = true;

                        break;
                    }
                }

                if ($hasChanges) {
                    $existingItem->update($updateData);

                    $this->updated++;
                } else {

                    $this->skipped++;
                }

                DB::commit();

                return null;
            }

            // Create new item
            $newItemData = [
                'ItemCode' => $itemCode,
                'BarCode' => $row['barcode'] ?? null,
                'ItemName' => $itemName,
                'ItemType' => $itemTypeId,
                'UOM' => $uomId,
                'InventoryType' => $inventoryTypeId,
                'Category' => $categoryId,
                'Status' => $statusId ?? $this->getDefaultStatusId(),
                'ItemDescription' => $row['itemdescription'] ?? null,
                'CreatedBy' => Auth::id(),
                'ModifiedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ];

            // Only add ItemPrice if provided
            if (! empty($row['itemprice']) && $priceId !== null) {
                $newItemData['ItemPrice'] = $priceId;
            }

            // Create the item
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
            Log::error("❌ Import failed for row {$rowNumber}: " . $e->getMessage(), [
                'row' => $row,
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Validate required fields with user-friendly error messages
     */
    private function validateRequiredFields(array $row, int $rowNumber): array
    {
        $errors = [];

        // Check all required fields
        $requiredFields = [
            'itemcode' => 'Item Code',
            'itemname' => 'Item Name',
            'itemtype' => 'Item Type',
            'uom' => 'UOM',
            'inventorytype' => 'Inventory Type',
            'category' => 'Category',
        ];

        foreach ($requiredFields as $field => $label) {
            $value = $row[$field] ?? $row[ucfirst($field)] ?? null;
            if (empty($value) || trim($value) === '') {
                $errors[] = "Row {$rowNumber}: {$label} is required";
            }
        }

        return $errors;
    }

    /**
     * Lookup CodeDetail ID by CodeID and Description
     */
    private function lookupCodeDetailId(string $codeId, string $description)
    {
        if (empty($description) || strtoupper($description) === 'N/A' || $description === '-') {
            return null;
        }

        $codeDetail = CodeDetail::where('CodeID', $codeId)
            ->where('Description', $description)
            ->first();

        return $codeDetail ? $codeDetail->ID : null;
    }

    /**
     * Lookup or create PriceManagement entry - OPTIONAL
     */
    private function lookupOrCreatePrice($priceValue)
    {
        if (empty($priceValue)) {
            return null;
        }

        // Try to clean the price value
        $priceValue = trim($priceValue);

        // Remove any currency symbols or commas
        $priceValue = preg_replace('/[^\d.]/', '', $priceValue);

        // Convert to float if possible
        $numericValue = floatval($priceValue);

        if ($numericValue <= 0) {
            return null;
        }

        // Try to find existing price
        $price = PriceManagement::where('ActualPrice', $numericValue)->first();

        if (! $price) {
            // Create new price entry if not found
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

    /**
     * Find category ID considering hierarchy
     */
    private function findCategoryId(?string $categoryName, ?string $parentCategoryName = null)
    {
        if (empty($categoryName) || $categoryName === '-') {
            return null;
        }

        $query = ItemCategories::where('Name', $categoryName);

        if (! empty($parentCategoryName) && $parentCategoryName !== '-') {
            // Look for subcategory with specified parent
            $query->whereHas('parent', function ($q) use ($parentCategoryName) {
                $q->where('Name', $parentCategoryName);
            });
        } else {
            // Look for main category (no parent)
            $query->whereNull('ParentId');
        }

        $category = $query->first();

        return $category ? $category->Id : null;
    }

    /**
     * Get default active status ID
     */
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
