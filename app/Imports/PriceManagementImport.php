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
            $uomId = null;
            if (!empty($data['uom'])) {
                $uom = UnitOfMeasure::where('Code', $data['uom'])->first();
                if ($uom) {
                    $uomId = $uom->Id;
                } else {
                    Log::warning("UOM not found for code: {$data['uom']}");
                }
            }


            PriceManagement::where('ItemID', $item->Id)
                ->where('ItemCode', $itemCode)
                ->where('DeletedOn', null)
                ->update([
                    'DeletedOn' => now(),
                    'DeletedBy' => auth()->id() ?? 1,
            ]);


            $created = PriceManagement::create([
                'PriceID'       => $data['priceid'] ?? null,
                'ItemID'        => $item->Id,
                'UOM'           => $uomId,
                'ActualPrice'   => (float) $actualPrice,
                'CurrencyCode'  => $data['currencycode'] ?? 'KES',
                'EffectiveFrom' => $this->parseDate($data['effectivefrom'] ?? null),
                'EffectiveTo'   => $this->parseDate($data['effectiveto'] ?? null),
                'IsDefault'     => isset($data['isdefault']) ? (int) $data['isdefault'] : 0,
                'Source'        => $data['source'] ?? null,
                'CreatedBy'     => auth()->id() ?? 1,
                'CreatedOn'     => now(),
                'ModifiedBy'    => auth()->id() ?? 1,
                'ModifiedOn'    => now(),
                'ItemCode'      => $itemCode,
            ]);

            $item->update([
    'ItemPrice' => $created->Id
]);

            Log::info("✅ PriceManagement created successfully", $created->toArray());

        } catch (\Exception $e) {
            Log::error("❌ Insert failed: " . $e->getMessage(), $data);
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
