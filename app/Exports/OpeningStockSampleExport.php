<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OpeningStockSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            ['1', '1', 'ITM-00001', '1', '1', '0', '1', '1', '1', '1', '5', '1', '3', '100000', '13/06/2025', '1'],
        ];
    }

    public function headings(): array
    {
        return [
            'CategoryId',
            'SubCategoryId',
            'ItemCode',
            'BatchTracked',
            'SerialTracked',
            'Perishable',
            'Saleable',
            'Purchasable',
            'BranchId',
            'StoreId',
            'QTY',
            'MinStockLevel',
            'ReorderQty',
            'MaxStockLevel',
            'LastReceivedDate',
            'IsActive'
        ];
    }
}

