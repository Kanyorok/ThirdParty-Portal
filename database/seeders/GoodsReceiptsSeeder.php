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
            [
                'GRNID' => 'GRN-001',
                'POID' => 1,
                'SupplierId' => 1,
                'ReceivedDate' => $now->copy()->subDays(3),
                'StoreID' => 'STR-101',
                'ReceivedBy' => 'John Doe',
                'InspectionStatus' => 'Accepted',
                'TransferStatus' => 'Transferred',
                'ItemNo' => 'ITM-001',
                'POQTY' => 10,
                'ReceivedQTY' => 10,
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
                'GRNID' => 'GRN-002',
                'POID' => 2,
                'SupplierId' => 2,
                'ReceivedDate' => $now->copy()->subDays(2),
                'StoreID' => 'STR-102',
                'ReceivedBy' => 'Jane Smith',
                'InspectionStatus' => 'Accepted',
                'TransferStatus' => 'Pending',
                'ItemNo' => 'ITM-002',
                'POQTY' => 5,
                'ReceivedQTY' => 5,
                'TransferTo' => 'IT Dept',
                'TagRequired' => false,
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
                'SupplierId' => 3,
                'ReceivedDate' => $now->copy()->subDays(1),
                'StoreID' => 'STR-103',
                'ReceivedBy' => 'Alex Kimani',
                'InspectionStatus' => 'Rejected',
                'TransferStatus' => 'N/A',
                'ItemNo' => 'ITM-003',
                'POQTY' => 7,
                'ReceivedQTY' => 4,
                'TransferTo' => 'Field Ops',
                'TagRequired' => true,
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
