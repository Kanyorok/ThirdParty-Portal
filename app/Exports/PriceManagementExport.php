<?php

namespace App\Exports;

use App\Models\Core\Currency;
use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\PriceManagement;
use App\Models\Inventory\UnitOfMeasure;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PriceManagementExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    public function array(): array
    {
        $data = [];
        $existingPrices = PriceManagement::with(['item', 'uom', 'currency'])
            ->whereNull('DeletedOn')
            ->orderBy('PriceID')
            ->get();

        if ($existingPrices->count() > 0) {
            foreach ($existingPrices as $price) {
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
            $data[] = ['---', '---', '---', '---', '---', '---', '---'];
        }
        $itemsWithoutPrices = ItemMasterList::with('uom')
            ->whereNull('DeletedOn')
            ->whereNotIn('Id', function ($query) {
                $query->select('ItemID')
                    ->from('t_Pricing')
                    ->whereNull('DeletedOn');
            })
            ->orderBy('ItemCode')
            ->get();

        foreach ($itemsWithoutPrices as $item) {
            $data[] = [
                '',
                $item->ItemCode,
                $item->ItemName,
                $item->uom?->Code ?? '',
                '',
                'KES',
                'No',
            ];
        }
        $data[] = [];
        $data[] = ['=== REFERENCE DATA (Do not modify rows below) ===', '', '', '', '', '', ''];
        $data[] = [];
        $data[] = ['Available Currencies:', '', '', '', '', '', ''];
        $currencies = Currency::orderBy('Code')->get();
        foreach ($currencies as $currency) {
            $data[] = [
                $currency->Code,
                $currency->Name,
                '', '', '', '', '',
            ];
        }
        $data[] = [];
        $data[] = ['Available Units of Measure (UOM):', '', '', '', '', '', ''];
        $uoms = UnitOfMeasure::where('Active', 1)->orderBy('Code')->get();
        foreach ($uoms as $uom) {
            $data[] = [
                $uom->Code,
                $uom->Name ?? '',
                '', '', '', '', '',
            ];
        }
        $data[] = [];
        $data[] = ['INSTRUCTIONS:', '', '', '', '', '', ''];
        $data[] = ['1. Items already with prices are listed at the top', '', '', '', '', '', ''];
        $data[] = ['2. Items without prices are pre-filled below the separator (---)', '', '', '', '', '', ''];
        $data[] = ['3. For items without prices: Just fill in the ActualPrice column', '', '', '', '', '', ''];
        $data[] = ['4. ItemCode, ItemName, and UOM are pre-filled from the system', '', '', '', '', '', ''];
        $data[] = ['5. You can change Currency (default: KES) and IsDefault (Yes/No)', '', '', '', '', '', ''];
        $data[] = ['6. To update existing prices: Change the ActualPrice - system will create a new version', '', '', '', '', '', ''];
        $data[] = ['7. Do not modify the reference data section at the bottom', '', '', '', '', '', ''];

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

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 15,
            'C' => 35,
            'D' => 10,
            'E' => 15,
            'F' => 15,
            'G' => 12,
        ];
    }
}
