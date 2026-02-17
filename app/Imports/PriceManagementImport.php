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

            if (isset($row['priceid']) && preg_match('/^[-=]+$/', $row['priceid'])) {
                $this->processed--;
                DB::rollBack();

                return null;
            }

            $hasAnyData = false;
            foreach ($row as $value) {
                if (! empty($value) && $value !== '-' && $value !== 'N/A' && $value !== '') {
                    $hasAnyData = true;

                    break;
                }
            }

            if (! $hasAnyData) {
                $this->processed--;
                DB::rollBack();

                return null;
            }

            $referenceIndicators = [
                'example', 'sample', 'reference', 'template', 'xxx', 'test',
                'e.g.', 'eg.', '<', '>',
            ];

            $itemCode = strtolower((string)($row['itemcode'] ?? ''));
            foreach ($referenceIndicators as $indicator) {
                if (str_contains($itemCode, $indicator)) {
                    $this->processed--;
                    DB::rollBack();

                    return null;
                }
            }

            $requiredColumns = ['itemcode', 'actualprice'];

            foreach ($requiredColumns as $col) {
                if (! array_key_exists($col, $row)) {
                    throw new \Exception("File format error: Missing required column '{$col}'. Please use the template.");
                }
            }

            $validationErrors = $this->validateRequiredFields($row, $rowNumber);
            if (! empty($validationErrors)) {
                $this->skipped++;
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
            if (! $item) {
                $this->skipped++;
                DB::rollBack();

                return null;
            }
            $uomId = $this->resolveUomId($uomCode, $item);
            if (! $uomId) {
                $this->skipped++;
                DB::rollBack();

                return null;
            }
            $currency = Currency::where('Code', $currencyCode)->first();
            if (! $currency) {
                $this->skipped++;
                DB::rollBack();

                return null;
            }
            $existingPrice = null;
            if (! empty($priceId)) {
                $existingPrice = PriceManagement::where('PriceID', $priceId)
                    ->whereNull('DeletedOn')
                    ->first();
            }

            if (! $existingPrice) {
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
                        $this->processed--;
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
            $message = $e->getMessage();
            if (str_contains($message, 'Duplicate entry')) {
                $errorMsg = "Row {$rowNumber}: Duplicate entry detected";
            } elseif (str_contains($message, 'foreign key constraint')) {
                $errorMsg = "Row {$rowNumber}: Invalid reference data";
            } elseif (str_contains($message, 'Data too long')) {
                $errorMsg = "Row {$rowNumber}: Data exceeds maximum length";
            } else {
                $errorMsg = config('app.debug')
                    ? "Row {$rowNumber}: " . $message
                    : "Row {$rowNumber}: Data error - please check values";
            }

            $this->errors[] = $errorMsg;

            return null;
        }
    }

    private function validateRequiredFields(array $row, int $rowNumber): array
    {
        $errors = [];
        $itemCode = $row['itemcode'] ?? null;
        if (empty($itemCode) || trim($itemCode) === '' || trim($itemCode) === '-') {
            $errors[] = "Row {$rowNumber}: Item Code is missing";
        }

        $actualPrice = $row['actualprice'] ?? null;
        if (empty($actualPrice) || trim($actualPrice) === '' || trim($actualPrice) === '-') {
            $errors[] = "Row {$rowNumber}: Price is missing";
        } elseif (! is_numeric($actualPrice) || (float)$actualPrice <= 0) {
            $errors[] = "Row {$rowNumber}: Price must be a positive number (got: {$actualPrice})";
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
        if (! empty($item->UOM)) {
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
        return ! empty($this->errors);
    }
}
