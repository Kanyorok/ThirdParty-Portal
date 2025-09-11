<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GoodsReceiptsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_GoodsReceipts')->insert([

            // Goods Receipt order 1
            [
                'GRNID' => 'GRN-001',
                'POID' => 'PO-0001',
                'SupplierId' => 1,
                'ReceivedDate' => $now->copy()->subDays(3),
                'StoreID' => 'STORE-001',
                'ReceivedBy' => '2',
                'InspectionStatus' => 'p',
                'TransferStatus' => '1',
                'ItemNo' => 1,
                'POQTY' => 4,
                'ReceivedQTY' => 4,
                'TransferTo' => '1',
                'TagRequired' => true,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'GRNID' => 'GRN-001',
                'POID' => 'PO-0001',
                'SupplierId' => 1,
                'ReceivedDate' => $now->copy()->subDays(2),
                'StoreID' => 'STORE-001',
                'ReceivedBy' => '2',
                'InspectionStatus' => 'p',
                'TransferStatus' => '1',
                'ItemNo' => 2,
                'POQTY' => 6,
                'ReceivedQTY' => 6,
                'TransferTo' => '1',
                'TagRequired' => false,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],

            //GRN Order 2
            [
                'GRNID' => 'GRN-002',
                'POID' => 'PO-0002',
                'SupplierId' => 2,
                'ReceivedDate' => $now->copy()->subDays(1),
                'StoreID' => 'STORE-001',
                'ReceivedBy' => '2',
                'InspectionStatus' => 'p',
                'TransferStatus' => '1',
                'ItemNo' => 3,
                'POQTY' => 10,
                'ReceivedQTY' => 10,
                'TransferTo' => '1',
                'TagRequired' => true,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'GRNID' => 'GRN-002',
                'POID' => 'PO-0002',
                'SupplierId' => 2,
                'ReceivedDate' => $now->copy()->subDays(5),
                'StoreID' => 'STORE-001',
                'ReceivedBy' => '2',
                'InspectionStatus' => 'p',
                'TransferStatus' => '1',
                'ItemNo' => 4,
                'POQTY' => 5,
                'ReceivedQTY' => 5,
                'TransferTo' => '1',
                'TagRequired' => false,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],

            //GRN Order 3
            [
                'GRNID' => 'GRN-003',
                'POID' => 'PO-0003',
                'SupplierId' => 3,
                'ReceivedDate' => $now->copy()->subDays(4),
                'StoreID' => 'STORE-001',
                'ReceivedBy' => '2',
                'InspectionStatus' => 'p',
                'TransferStatus' => '1',
                'ItemNo' => 5,
                'POQTY' => 7,
                'ReceivedQTY' => 7,
                'TransferTo' => '1',
                'TagRequired' => true,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
            [
                'GRNID' => 'GRN-003',
                'POID' => 'PO-0003',
                'SupplierId' => 3,
                'ReceivedDate' => $now->copy()->subDays(6),
                'StoreID' => 'STORE-001',
                'ReceivedBy' => '2',
                'InspectionStatus' => 'p',
                'TransferStatus' => '1',
                'ItemNo' => 6,
                'POQTY' => 5,
                'ReceivedQTY' => 4,
                'TransferTo' => '1',
                'TagRequired' => false,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null,
            ],
        ]);
    }
}
