<?php

namespace App\Exports;

use App\Models\Inventory\ItemMasterList;
use App\Models\Inventory\Store;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OpeningStockSampleExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        // Load items with related stock + category
        $items = ItemMasterList::with(['stockItems', 'category'])->get();

        $data = [];

        foreach ($items as $item) {
            $stock = $item->stockItems->first();

            $data[] = [
                $item->ItemCode ?? '',
                $item->ItemName ?? '',
                $stock?->Saleable ?? 1,
                $stock?->Purchasable ?? 1,
                '', // left empty for user input (BranchName)
                '', // left empty for user input (StoreName)
                $item->stockItems->sum('CurrentQty') ?? 0,
                $stock?->Min ?? 0,
                $stock?->Reorder ?? 0,
                $stock?->Max ?? 0,
                $item->uom->Code ?? '',
                $item->price->ActualPrice ?? null,
                $stock?->LastReceived ?? now()->format('d/m/Y'),
                $stock?->Status ?? 1,
            ];
        }

        $data[] = [];
        $data[] = ['--- "1" Refers to Active or Yes and "0" refers to Inactive or No  ---'];
        $data[] = ['--- Pricing Should Come from the Price Management Module  ---'];
        $data[] = [];
        $data[] = ['--- Reference: Available Stores & Branches ---'];

        $stores = Store::with('branch')->get();
        foreach ($stores as $store) {
            $data[] = [
                'Branch' => $store->branch?->Name ?? 'N/A',
                'Store' => $store->StoreName,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            'ItemCode',
            'ItemName',
            'Saleable',
            'Purchasable',
            'BranchName',   // blank for user input
            'StoreName',    // blank for user input
            'QTY',
            'MinStockLevel',
            'ReorderQty',
            'MaxStockLevel',
            'UOM',
            'ItemPrice',
            'LastReceivedDate',
            'IsActive',
        ];
    }
}
