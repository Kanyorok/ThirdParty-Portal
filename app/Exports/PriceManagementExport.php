<?php

namespace App\Exports;

use App\Models\Core\Currency;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PriceManagementExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        $prices = PriceManagement::with(['item', 'uom', 'currency'])
            ->whereNull('DeletedOn')
            ->orderBy('PriceID')
            ->get();

        $data = [];

        foreach ($prices as $price) {
            $data[] = [
                $price->PriceID ?? '',
                $price->item?->ItemCode ?? '',
                $price->item?->ItemName ?? '',
                $price->uom?->Code ?? '',
                $price->ActualPrice ?? '0.00',
                $price->currency?->Code ?? 'KES',
                $price->IsDefault ? 'Yes' : 'No',
            ];
        }

        $data[] = [];
        $data[] = ['--- REFERENCE DATA (Do not modify this section) ---'];
        $data[] = [];

        $data[] = ['--- Available Items ---'];
        $items = ItemMasterList::with('uom')
            ->whereNull('DeletedOn')
            ->orderBy('ItemCode')
            ->get();
        foreach ($items as $item) {
            $data[] = [
                'ItemCode: ' . $item->ItemCode,
                'ItemName: ' . $item->ItemName,
                'UOM: ' . ($item->uom?->Code ?? 'N/A'),
            ];
        }
        $data[] = [];

        $data[] = ['--- Available Units of Measure (UOM) ---'];
        foreach (UnitOfMeasure::where('Active', 1)->orderBy('Code')->get() as $uom) {
            $data[] = ['UOM: ' . $uom->Code];
        }
        $data[] = [];

        $data[] = ['--- Available Currencies ---'];
        foreach (Currency::orderBy('Code')->get() as $currency) {
            $data[] = [
                'Currency: ' . $currency->Code,
                'Name: ' . $currency->Name,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'PriceID',
            'ItemCode',
            'ItemName',
            'UOM',
            'ActualPrice',
            'CurrencyCode',
            'IsDefault',
        ];
    }
}
