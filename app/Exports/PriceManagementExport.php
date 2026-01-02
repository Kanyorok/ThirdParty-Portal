<?php

namespace App\Exports;

use App\Models\Inventory\ItemMasterList;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PriceManagementExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $items = ItemMasterList::with('price', 'uom')->get();

        return $items->map(function ($item) {
            return [
                // Format PriceID on the fly from price->Id
                isset($item->Id) ? 'PR-' . str_pad($item->Id, 5, '0', STR_PAD_LEFT) : '-',
                $item->Id ?? '-',
                $item->ItemCode ?? '-',
                $item->uom?->Code ?? '-',
                $item->price->ActualPrice ?? '0.00',
                $item->price->CurrencyCode ?? '-',


            ];
        })->toArray();
    }

    public function headings(): array
    {
        return [
            'PriceID',
            'ItemID',
            'ItemCode',
            'UOM',
            'ActualPrice',
            'CurrencyCode',

        ];
    }
}
