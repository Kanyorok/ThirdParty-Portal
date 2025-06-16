<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OpeningStockSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['2', '2', 'ITM-00001', '13/06/2025', '10', '1', '30000', 'Purchased internally'],
        ];
    }

    public function headings(): array
    {
        return [
            'BranchId',
            'StoreId',
            'ItemCode',
            'Date',
            'Quantity',
            'UOM',
            'Value',
            'Remarks',
        ];
    }
}

