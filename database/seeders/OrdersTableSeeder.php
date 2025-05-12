<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrdersTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('t_Orders')->insert([
            'DocType' => 1,
            'DocVersion' => 1,
            'DocState' => 1,
            'DocFlag' => 0,
            'OrderNo' => 'ORD0001',
            'InvNo' => 'INV0001',
            'GrvNo' => 'GRV0001',
            'ReqNo' => 'REQ0001',
            'GrvID' => 101,
            'AccountID' => 2001,
            'Description' => 'Initial test order',
            'OrderDate' => Carbon::now(),
            'InvDate' => Carbon::now(),
            'TaxInclusive' => true,
            'DeliveryDate' => Carbon::now()->addDays(3),
            'ReturnDate' => Carbon::now()->addDays(10),
            'Message1' => 'Thank you for your business.',
            'Message2' => null,
            'Message3' => null,
            'ExtOrdNum' => 'EXT12345',
            'InvDisc' => 5.00,
            'InvDiscReasonID' => 'PROMO',
            'InvDiscAmnt' => 100.00,
            'InvDiscAmntEx' => 95.00,
            'InvTotExclDEx' => 950.00,
            'InvTotTaxDEx' => 100.00,
            'InvTotInclDEx' => 1050.00,
            'InvTotExcl' => 950.00,
            'InvTotTax' => 100.00,
            'InvTotIncl' => 1050.00,
            'OrdDiscAmnt' => 50.00,
            'OrdDiscAmntEx' => 45.00,
            'OrdTotExclDEx' => 900.00,
            'OrdTotTaxDEx' => 90.00,
            'OrdTotInclDEx' => 990.00,
            'OrdTotExcl' => 900.00,
            'OrdTotTax' => 90.00,
            'OrdTotIncl' => 990.00,
            'fInvDiscAmntForeign' => 10.00,
            'fInvDiscAmntExForeign' => 9.50,
            'fInvTotExclDExForeign' => 95.00,
            'fInvTotTaxDExForeign' => 10.00,
            'fInvTotInclDExForeign' => 105.00,
            'fInvTotExclForeign' => 95.00,
            'fInvTotTaxForeign' => 10.00,
            'fInvTotInclForeign' => 105.00,
            'fOrdDiscAmntForeign' => 5.00,
            'fOrdDiscAmntExForeign' => 4.50,
            'fOrdTotExclDExForeign' => 90.00,
            'fOrdTotTaxDExForeign' => 9.00,
            'fOrdTotInclDExForeign' => 99.00,
            'fOrdTotExclForeign' => 90.00,
            'fOrdTotTaxForeign' => 9.00,
            'fOrdTotInclForeign' => 99.00,
            'DeliveryNote' => 'Handle with care.',
            'Terms' => '30 Days Net',
            'Priority' => 'High',
            'BranchID' => 'BR001',
            'CreatedBy' => 1,
            'CreatedOn' => Carbon::now(),
            'ModifiedBy' => 1,
            'ModifiedOn' => Carbon::now(),
            'DeletedBy' => null,
            'DeletedOn' => null,
        ]);
    }
}
