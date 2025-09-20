<?php

namespace App\Imports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PriceManagementImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $itemCode    = $data['itemcode'] ?? null;
        $actualPrice = $data['actualprice'] ?? null;

        if (empty($itemCode) || empty($actualPrice)) {
            Log::warning("Invalid row skipped (missing itemcode or actualprice)", $data);
            return;
        }

        $item = ItemMasterList::where('ItemCode', $itemCode)->first();
        if (!$item) {
            Log::warning("Item not found for ItemCode: {$itemCode}", $data);
            return;
        }

        try {
            // Resolve UOM
            $uomId = null;
            if (!empty($data['uom'])) {
                $uom = UnitOfMeasure::where('Code', $data['uom'])->first();
                if ($uom) {
                    $uomId = $uom->Id;
                } else {
                    Log::warning("UOM not found for code: {$data['uom']}");
                }
            }

            // Normalize incoming row
            $newData = [
                'ItemID'        => $item->Id,
                'ItemCode'      => $itemCode,
                'UOM'           => $uomId,
                'ActualPrice'   => (float) $actualPrice,
                'CurrencyCode'  => $data['currencycode'] ?? 'KES',
                'EffectiveFrom' => $this->parseDate($data['effectivefrom'] ?? null),
                'EffectiveTo'   => $this->parseDate($data['effectiveto'] ?? null),
                'IsDefault'     => isset($data['isdefault']) ? (int) $data['isdefault'] : 0,
                'Source'        => $data['source'] ?? null,
            ];

            // Find the latest active price for this item
            $latest = PriceManagement::where('ItemID', $item->Id)
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
                    'DeletedBy' => auth()->id() ?? 1,
                ]);

                // Reuse PriceID
                $priceId = $latest->PriceID;
            } else {
                $priceId = $data['priceid'] ?? null;
            }

            // Create new price record
            $created = PriceManagement::create(array_merge($newData, [
                'PriceID'    => $priceId,
                'CreatedBy'  => auth()->id() ?? 1,
                'CreatedOn'  => now(),
                'ModifiedBy' => auth()->id() ?? 1,
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
        try {
            return $value ? Carbon::parse($value)->format('Y-m-d') : null;
        } catch (\Throwable $e) {
            Log::warning("Invalid date format: {$value}");
            return null;
        }
    }
}
