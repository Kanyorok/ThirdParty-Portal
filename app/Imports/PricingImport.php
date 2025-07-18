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

            // Unique matching fields
            $match = [
                'ItemID'        => $row[0],
                'UOM'           => $row[1],
            ];

            // Values to update or insert
            $values = [
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

            // If inserting new, add creator metadata
            $existing = PriceManagement::where($match)->first();
            if (!$existing) {
                $values['CreatedBy'] = $userId;
                $values['CreatedOn'] = now();
            }

            // Perform update or insert
            $pricing = PriceManagement::updateOrCreate($match, $values);

            // Set PriceID if it's a newly inserted record
            if (!$pricing->PriceID) {
                $pricing->PriceID = 'PR-' . str_pad($pricing->Id, 5, '0', STR_PAD_LEFT);
                $pricing->save();
            }

            $item = ItemMasterList::find($pricing->ItemID);

            if ($item) {
                // Only update ItemPrice if:
                // - No price set
                // - OR current pricing is default
                // - OR this pricing is newer than existingg one
                if (
                    !$item->ItemPrice ||
                    $pricing->IsDefault ||
                    $item->ItemPrice != $pricing->Id
                ) {
                    $item->ItemPrice = $pricing->Id;
                    $item->save();
                }
            }
            activity()
                ->performedOn($pricing)
                ->causedBy(Auth::user())
                ->event('create')
                ->withProperties(['action' => 'create'])
                ->log('Created or Updated Item Pricing from import');
        }
    }

}