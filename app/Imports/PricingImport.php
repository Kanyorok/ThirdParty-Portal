<?php

namespace App\Imports;

use App\Models\Inventory\PriceManagement;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use App\Models\Inventory\ItemMasterList;
use Carbon\Carbon;

class PricingImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $userId = auth()->id() ?? 1;
        $header = true;

        foreach ($rows as $row) {
            if ($header) {
                $header = false;
                continue;
            }

            if (count($row) < 8) {
                Log::warning('Invalid row skipped due to insufficient columns', $row->toArray());
                continue;
            }

            $itemCode = trim($row[0]);

            // 🔍 Get ItemMasterList by ItemCode
            $item = ItemMasterList::where('ItemCode', $itemCode)->first();

            if (!$item) {
                Log::warning("ItemCode '{$itemCode}' not found — skipping row", $row->toArray());
                continue;
            }

            $itemId = $item->Id;

            $isDefault = filter_var($row[7], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

            try {
                $effectiveFrom = is_numeric($row[4])
                    ? Carbon::instance(Date::excelToDateTimeObject($row[4]))
                    : Carbon::parse($row[4]);

                $effectiveTo = is_numeric($row[5])
                    ? Carbon::instance(Date::excelToDateTimeObject($row[5]))
                    : Carbon::parse($row[5]);
            } catch (\Exception $e) {
                Log::warning('Invalid date format', $row->toArray());
                continue;
            }

            // Matching criteria (ItemID + UOM)
            $match = [
                'ItemID'        => $itemId,
                'UOM'           => $row[1],
            ];

            // Fields to insert or update
            $values = [
                'ItemCode'      => $itemCode,
                'EffectiveFrom' => $effectiveFrom,
                'EffectiveTo'   => $effectiveTo,
                'EstimatedPrice'=> $row[2],
                'ActualPrice'   => $row[3],
                'CurrencyCode'  => $row[6],
                'IsDefault'     => $isDefault,
                'Source'        => 'ExcelImport',
                'ModifiedBy'    => $userId,
                'ModifiedOn'    => now(),
            ];

            $existing = PriceManagement::where($match)->first();
            if (!$existing) {
                $values['CreatedBy'] = $userId;
                $values['CreatedOn'] = now();
            }

            $pricing = PriceManagement::updateOrCreate($match, $values);

            // Assign PriceID if not set
            if (!$pricing->PriceID) {
                $pricing->PriceID = 'PR-' . str_pad($pricing->Id, 5, '0', STR_PAD_LEFT);
                $pricing->save();
            }

            // Update ItemMasterList.ItemPrice only if new, default, or changed
            if (
                !$item->ItemPrice ||
                $pricing->IsDefault ||
                $item->ItemPrice != $pricing->Id
            ) {
                $item->ItemPrice = $pricing->Id;
                $item->save();
            }

            // Log the actual action
            $action = $existing ? 'updated' : 'created';

            activity()
                ->performedOn($pricing)
                ->causedBy(Auth::user())
                ->event($action)
                ->withProperties([
                    'action' => $action,
                    'item_code' => $itemCode,
                    'item_id' => $itemId,
                    'uom' => $pricing->UOM,
                ])
                ->log(ucfirst($action) . ' Item Pricing from import');
        }
    }
}
