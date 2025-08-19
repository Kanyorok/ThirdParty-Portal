<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use App\Models\Inventory\PriceManagement;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PriceManagementImport implements OnEachRow, WithHeadingRow
{
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // Basic validation or logging
        if (!isset($data['ItemCode']) || !isset($data['Price'])) {
            Log::warning("Invalid row skipped", $data);
            return;
        }
        try {
            DB::table('t_Pricing')->insert([
                'ItemID' => $data['ItemID'],  // or your mapped field
                'UOMCode' => $data['UOMCode'],
                //'EstimatedPrice' => $data['EstimatedPrice'], // or EstimatedPrice if preferred
                'ActualPrice' => $data['ActualPrice'],
                'EffectiveFrom' => $data['EffectiveFrom'],
                'EffectiveTo' => $data['EffectiveTo'],
                'CurrencyCode' => $data['Currency'],
                'IsDefault' => isset($data['IsDefault']) ? (bool)$data['IsDefault'] : false,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
                'CreatedBy' => auth()->id() ?? 1,
                'ModifiedBy' => auth()->id() ?? 1,
            ]);
        } catch (\Exception $e) {
            Log::error("Insert failed: " . $e->getMessage());
        }
    }
}

