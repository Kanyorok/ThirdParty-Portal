<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcurementRequisitionLinesSeeder extends Seeder
{
    public function run(): void
    {
        $requisitionId = DB::table('t_Requisitions')->where('RequisitionNo', 'REQ-2025-004')->value('Id');
        $itemId = DB::table('t_Items')->value('Id');
        $urgencyId = DB::table('t_CodeDetails')->where('CodeID', 'RequisitionUrgency')->value('ID');
        $statusId = DB::table('t_CodeDetails')->where('CodeID', 'a')->value('ID');
        $userId = DB::table('t_Users')->value('Id');
        $uomCode = DB::table('t_UOM')->value('Id');


        if (! $requisitionId || ! $itemId || ! $urgencyId || ! $statusId || ! $userId || ! $uomCode) {
            // dump('Missing required line item dependencies.');
            return;
        }

        DB::table('t_RequisitionLines')->insert([
            'RequisitionID' => $requisitionId,
            'Type' => 'Standard',
            'Item' => $itemId,
            'Description' => 'Seeder test item description',
            'UOM' => $uomCode,
            'Quantity' => 15,
            'ExpectedPrice' => 500.00,
            'UrgencyID' => $urgencyId,
            'StatusID' => $statusId,
            'CreatedBy' => $userId,
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => $userId,
            'ModifiedOn' => Carbon::now(),
            'DeletedBy' => null,
        ]);
    }
}
