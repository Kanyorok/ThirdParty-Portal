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
                'POID' => 1,
                'SupplierId' => 1,
                'ReceivedDate' => $now->copy()->subDays(3),
                'StoreID' => 'STR-101',
                'ReceivedBy' => 'John Doe',
                'InspectionStatus' => 'Accepted',
                'TransferStatus' => 'Transferred',
                'ItemNo' => 1,
                'POQTY' => 4,
                'ReceivedQTY' => 4,
                'TransferTo' => 'Main Warehouse',
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
                'POID' => 1,
                'SupplierId' => 2,
                'ReceivedDate' => $now->copy()->subDays(2),
                'StoreID' => 'STR-102',
                'ReceivedBy' => 'Jane Smith',
                'InspectionStatus' => 'Accepted',
                'TransferStatus' => 'Pending',
                'ItemNo' => 2,
                'POQTY' => 6,
                'ReceivedQTY' => 6,
                'TransferTo' => 'IT Dept',
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
                'POID' => 2,
                'SupplierId' => 3,
                'ReceivedDate' => $now->copy()->subDays(1),
                'StoreID' => 'STR-103',
                'ReceivedBy' => 'Alex Kimani',
                'InspectionStatus' => 'Rejected',
                'TransferStatus' => 'N/A',
                'ItemNo' => 3,
                'POQTY' => 10,
                'ReceivedQTY' => 10,
                'TransferTo' => 'Field Ops',
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
                'POID' => 2,
                'SupplierId' => 4,
                'ReceivedDate' => $now->copy()->subDays(5),
                'StoreID' => 'STR-104',
                'ReceivedBy' => 'Mary Wambui',
                'InspectionStatus' => 'Accepted',
                'TransferStatus' => 'Transferred',
                'ItemNo' => 4,
                'POQTY' => 5,
                'ReceivedQTY' => 5,
                'TransferTo' => 'Main Warehouse',
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
                'POID' => 3,
                'SupplierId' => 5,
                'ReceivedDate' => $now->copy()->subDays(4),
                'StoreID' => 'STR-105',
                'ReceivedBy' => 'David Mwangi',
                'InspectionStatus' => 'Pending',
                'TransferStatus' => 'Pending',
                'ItemNo' => 5,
                'POQTY' => 7,
                'ReceivedQTY' => 7,
                'TransferTo' => 'IT Dept',
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
                'POID' => 3,
                'SupplierId' => 6,
                'ReceivedDate' => $now->copy()->subDays(6),
                'StoreID' => 'STR-106',
                'ReceivedBy' => 'Grace Njeri',
                'InspectionStatus' => 'Accepted',
                'TransferStatus' => 'Transferred',
                'ItemNo' => 6,
                'POQTY' => 5,
                'ReceivedQTY' => 4,
                'TransferTo' => 'Field Ops',
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
