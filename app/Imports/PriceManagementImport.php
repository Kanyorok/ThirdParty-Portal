<?php

namespace App\Imports;

use App\Models\Core\Currency;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PriceManagementImport implements ToModel, WithHeadingRow
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

            if (isset($row['priceid']) && str_starts_with($row['priceid'], '---')) {
                DB::rollBack();
                return null;
            }

            if (empty($row['itemcode']) && empty($row['actualprice'])) {
                DB::rollBack();
                return null;
            }

            $requiredColumns = ['itemcode', 'actualprice'];
            
            foreach ($requiredColumns as $col) {
                if (!array_key_exists($col, $row)) {
                    throw new \Exception("The uploaded file is missing required column: {$col}");
                }
            }

            $validationErrors = $this->validateRequiredFields($row, $rowNumber);
            if (!empty($validationErrors)) {
                $this->skipped++;
                $this->errors = array_merge($this->errors, $validationErrors);
                DB::rollBack();
                return null;
            }

            $priceId = $row['priceid'] ?? null;
            $itemCode = $row['itemcode'];
            $actualPrice = (float) $row['actualprice'];
            $uomCode = $row['uom'] ?? null;
            $currencyCode = $row['currencycode'] ?? 'KES';
            $isDefault = $this->parseBoolean($row['isdefault'] ?? 'No');

            $item = ItemMasterList::where('ItemCode', $itemCode)->first();
            if (!$item) {
                $errorMsg = "Row {$rowNumber}: Item with code '{$itemCode}' not found";
                $this->skipped++;
                $this->errors[] = $errorMsg;
                DB::rollBack();
                return null;
            }

            $uomId = $this->resolveUomId($uomCode, $item);
            if (!$uomId) {
                $errorMsg = "Row {$rowNumber}: Invalid UOM '{$uomCode}' for item '{$itemCode}'";
                $this->skipped++;
                $this->errors[] = $errorMsg;
                DB::rollBack();
                return null;
            }

            $currency = Currency::where('Code', $currencyCode)->first();
            if (!$currency) {
                $errorMsg = "Row {$rowNumber}: Currency '{$currencyCode}' not found";
                $this->skipped++;
                $this->errors[] = $errorMsg;
                DB::rollBack();
                return null;
            }

            $existingPrice = null;
            if (!empty($priceId)) {
                $existingPrice = PriceManagement::where('PriceID', $priceId)
                    ->whereNull('DeletedOn')
                    ->first();
            }

            if (!$existingPrice) {
                $existingPrice = PriceManagement::where('ItemID', $item->Id)
                    ->where('UOM', $uomId)
                    ->whereNull('DeletedOn')
                    ->latest('CreatedOn')
                    ->first();
            }

            if ($existingPrice) {
                $priceChanged = ((float) $existingPrice->ActualPrice) != $actualPrice;

                if ($priceChanged) {

                    $existingPrice->DeletedOn = now();
                    $existingPrice->DeletedBy = Auth::id();
                    $existingPrice->save();

                    $newPrice = new PriceManagement([
                        'PriceID' => $existingPrice->PriceID, 
                        'ItemID' => $item->Id,
                        'UOM' => $uomId,
                        'ActualPrice' => $actualPrice,
                        'CurrencyCode' => $currency->Id,
                        'IsDefault' => $isDefault,
                        'CreatedBy' => Auth::id(),
                        'CreatedOn' => now(),
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                    $newPrice->save();

                    $item->ItemPrice = $newPrice->Id;
                    $item->save();

                    $this->updated++;

                    activity()
                        ->causedBy(Auth::user())
                        ->performedOn($newPrice)
                        ->event('price_updated')
                        ->log("Price updated via Excel import (new version created)");

                    DB::commit();
                    return null;

                } else {
                    $hasOtherChanges = false;

                    if ($existingPrice->CurrencyCode != $currency->Id) {
                        $hasOtherChanges = true;
                    }
                    if ($existingPrice->IsDefault != $isDefault) {
                        $hasOtherChanges = true;
                    }

                    if ($hasOtherChanges) {
                        $existingPrice->CurrencyCode = $currency->Id;
                        $existingPrice->IsDefault = $isDefault;
                        $existingPrice->ModifiedBy = Auth::id();
                        $existingPrice->ModifiedOn = now();
                        $existingPrice->save();

                        $this->updated++;

                        activity()
                            ->causedBy(Auth::user())
                            ->performedOn($existingPrice)
                            ->event('updated')
                            ->log('Price metadata updated via Excel import');

                        DB::commit();
                        return null;
                    } else {
                        // No changes at all
                        $this->skipped++;
                        DB::commit();
                        return null;
                    }
                }
            }

            $newPrice = new PriceManagement([
                'PriceID' => $priceId, 
                'ItemID' => $item->Id,
                'UOM' => $uomId,
                'ActualPrice' => $actualPrice,
                'CurrencyCode' => $currency->Id,
                'IsDefault' => $isDefault,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);
            $newPrice->save();

            if (empty($newPrice->PriceID)) {
                $newPrice->PriceID = 'PR-' . str_pad($newPrice->Id, 5, '0', STR_PAD_LEFT);
                $newPrice->save();
            }

            $item->ItemPrice = $newPrice->Id;
            $item->save();

            $this->created++;
            DB::commit();

            activity()
                ->causedBy(Auth::user())
                ->performedOn($newPrice)
                ->event('imported')
                ->log('Price imported via Excel');

            return $newPrice;

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
            'actualprice' => 'Actual Price',
        ];

        foreach ($requiredFields as $field => $label) {
            $value = $row[$field] ?? null;
            if (empty($value) || trim($value) === '') {
                $errors[] = "Row {$rowNumber}: {$label} is required";
            }
        }

        if (!empty($row['actualprice'])) {
            if (!is_numeric($row['actualprice']) || (float)$row['actualprice'] <= 0) {
                $errors[] = "Row {$rowNumber}: Actual Price must be a positive number";
            }
        }

        return $errors;
    }

    
    private function parseBoolean($value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return ((int)$value) ? 1 : 0;
        }
        
        $normalized = strtolower(trim((string)$value));
        $truthy = ['1', 'true', 'yes', 'y', 'on', '✓', 'check', 'checked'];
        
        return in_array($normalized, $truthy, true) ? 1 : 0;
    }

   
    private function resolveUomId($uomInput, ItemMasterList $item): ?int
    {
        if ($uomInput !== null && $uomInput !== '' && $uomInput !== '-') {
            $candidate = trim((string)$uomInput);
            
            if (ctype_digit($candidate)) {
                $uom = UnitOfMeasure::where('Id', (int)$candidate)
                    ->where('Active', 1)
                    ->first();
                if ($uom) {
                    return (int)$uom->Id;
                }
            }
            
            $uom = UnitOfMeasure::where('Code', $candidate)
                ->where('Active', 1)
                ->first();
            if ($uom) {
                return (int)$uom->Id;
            }
        }

        if (!empty($item->UOM)) {
            return (int)$item->UOM;
        }

        return null;
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
        return !empty($this->errors);
    }
}