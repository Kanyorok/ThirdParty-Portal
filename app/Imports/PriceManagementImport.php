<?php

namespace App\Imports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class PriceManagementImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $itemCode = $data['itemcode'] ?? $data['item_code'] ?? null;
        $actualPrice = $data['actualprice'] ?? $data['price'] ?? null;

        if (empty($itemCode) || empty($actualPrice)) {
            
            return;
        }

        $item = ItemMasterList::where('ItemCode', $itemCode)->first();
        if (!$item) {
           
            return;
        }

        try {
            // Resolve UOM (accepts UOM code or numeric Id; falls back to item's UOM)
            $uomInput = $data['uom'] ?? $data['uomcode'] ?? $data['uom_code'] ?? null;
            $uomId = $this->resolveUomId($uomInput, $item);

            // Normalize incoming row
            $newData = [
                'ItemID'        => $item->Id,
                'ItemCode'      => $itemCode,
                'UOM'           => $uomId,
                'ActualPrice'   => (float) $actualPrice,
                'CurrencyCode'  => $data['currencycode'] ?? $data['currency'] ?? 'KES',
                // 'EffectiveFrom' => $this->parseDate($data['effectivefrom'] ?? $data['effective_from'] ?? null),
                // 'EffectiveTo'   => $this->parseDate($data['effectiveto'] ?? $data['effective_to'] ?? null),
                'IsDefault'     => $this->parseBoolean($data['isdefault'] ?? $data['default'] ?? $data['is_default'] ?? 0),
                // 'Source'        => $data['source'] ?? 'ExcelImport',
            ];

            // Find the latest active price for this item and UOM
            $latest = PriceManagement::where('ItemID', $item->Id)
                ->where('UOM', $uomId)
                ->whereNull('DeletedOn')
                ->latest('CreatedOn')
                ->first();

            if ($latest) {
                // Compare all business fields
                $hasChanges = false;
                foreach ($newData as $field => $val) {
                    if (($latest->$field ?? null) != ($val ?? null)) {
                        $hasChanges = true;
                        break;
                    }
                }

                if (!$hasChanges) {
                    // Nothing changed → skip
                    Log::info("⏭ No changes for ItemCode: {$itemCode}, skipping");
                    return;
                }

                // Soft delete old record
                $latest->update([
                    'DeletedOn' => now(),
                    'DeletedBy' => Auth::id() ?? 1,
                ]);

                // Reuse PriceID
                $priceId = $latest->PriceID;
            } else {
                $priceId = $data['priceid'] ?? null;
            }

            // Create new price record
            $created = PriceManagement::create(array_merge($newData, [
                'PriceID' => $priceId,
                'CreatedBy' => Auth::id() ?? 1,
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id() ?? 1,
                'ModifiedOn' => now(),
            ]));

            // Update item’s active price pointer
            $item->update([
                'ItemPrice' => $created->Id,
            ]);

            Log::info("✅ PriceManagement created/updated for ItemCode: {$itemCode}", $created->toArray());

        } catch (\Exception $e) {
            Log::error("❌ Import failed: " . $e->getMessage(), $data);
        }
    }

    /**
     * Safely parse a date or return null
     */
    private function parseDate($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        try {
            // Handle Excel serial numbers and numeric-like strings
            if (is_numeric($value)) {
                $dateTime = ExcelDate::excelToDateTimeObject((float)$value);
                return Carbon::instance($dateTime)->format('Y-m-d');
            }
            // Fallback to Carbon parsing for string dates
            return Carbon::parse((string)$value)->format('Y-m-d');
        } catch (\Throwable $e) {
            Log::warning("Invalid date format: {$value}");
            return null;
        }
    }

    /**
     * Parse various boolean representations including Excel formulas
     */
    private function parseBoolean($value): int
    {
        if (is_bool($value)) {
            return $value ? 1 : 0;
        }
        if (is_numeric($value)) {
            return ((int)$value) ? 1 : 0;
        }
        $normalized = strtolower(trim((string)$value));
        $truthy = [
            '1', 'true', 'yes', 'y', 'on', '=true()', '✓', 'check', 'checked'
        ];
        $falsy = [
            '0', 'false', 'no', 'n', 'off', '=false()'
        ];
        if (in_array($normalized, $truthy, true)) {
            return 1;
        }
        if (in_array($normalized, $falsy, true)) {
            return 0;
        }
        return 0;
    }

    /**
     * Resolve UOM Id from input value (code or id). Fallback to item's UOM.
     */
    private function resolveUomId($uomInput, ItemMasterList $item): ?int
    {
        // If explicit numeric Id provided
        if ($uomInput !== null && $uomInput !== '') {
            $candidate = trim((string)$uomInput);
            if (ctype_digit($candidate)) {
                $uom = UnitOfMeasure::where('Id', (int)$candidate)->first();
                if ($uom) {
                    return (int)$uom->Id;
                }
            }
            // Try by code
            $uom = UnitOfMeasure::where('Code', $candidate)->first();
            if ($uom) {
                return (int)$uom->Id;
            }
            Log::warning("UOM not found for input: {$candidate}");
        }

        // Fallback to item's configured UOM
        if (!empty($item->UOM)) {
            return (int)$item->UOM;
        }
        return null;
    }
}
