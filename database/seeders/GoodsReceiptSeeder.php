<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Procurement\GoodsReceipt;

class GoodsReceiptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed some example categories
        GoodsReceipt::create([
            'GRNID'            => 'GRN0001',
            'POID'             => 'PO12345',
            'ReceivedDate'     => '2024-05-01',
            'StoreID'          => 10,
            'ReceivedBy'       => 'John Doe',
            'InspectionStatus' => 'Passed',
            'TransferStatus'   => 'Pending',
            'ItemNo'           => 101,
            'POQTY'            => 50,
            'ReceivedQTY'      => 48,
            'TransferTo'       => 'Central Warehouse',
            'CreatedBy'        => 2,
            'ModifiedBy'       => 2,
        ]);
        GoodsReceipt::create([
            'GRNID'            => 'GRN0002',
            'POID'             => 'PO12346',
            'ReceivedDate'     => '2024-05-01',
            'StoreID'          => 10,
            'ReceivedBy'       => 'John Doe',
            'InspectionStatus' => 'Passed',
            'TransferStatus'   => 'Pending',
            'ItemNo'           => 101,
            'POQTY'            => 50,
            'ReceivedQTY'      => 48,
            'TransferTo'       => 'Central Warehouse',
            'CreatedBy'        => 1,
            'ModifiedBy'       => 1,
        ]);
    }
}