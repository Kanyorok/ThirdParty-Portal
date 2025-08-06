<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinanceTransactionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('t_TransactionTypes')->insert([
            [
                'Code' => 'GoodsReceipt',
                'Name' => 'Goods Receipt',
                'Description' => 'Transaction type for goods receipt',
                'IsActive' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'ServiceReceipt', 
                'Name'=>'Service Receipt', 
                'Description'=>'GRN for non-stock items and services',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'InvoiceBooking', 
                'Name'=>'Invoice Booking', 
                'Description'=>'Invoice matched to GRN or direct booking',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'PurchaseReturn', 
                'Name'=>'Purchase Return', 
                'Description'=>'Invoice matched to GRN or direct booking',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'StockIssue', 
                'Name'=>'Stock Issue', 
                'Description'=>'Issue of stock for consumption',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'StockAdjustment', 
                'Name'=>'Stock Adjustment', 
                'Description'=>'Write-off or shrinkage',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'RentInvoicing', 
                'Name'=>'Rent Invoicing', 
                'Description'=>'Rental invoice raised to tenant',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'PropertyReceipts', 
                'Name'=>'Property Receipts', 
                'Description'=>'Rent or utility payments received',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'FuelIssue', 
                'Name'=>'Fuel Issue', 
                'Description'=>'Fuel usage or fueling transaction',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'MaintenanceCost', 
                'Name'=>'Maintenance Cost', 
                'Description'=>'Fleet maintenance or repair expense',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'SettlementPayment', 
                'Name'=>'Settlement Payment', 
                'Description'=>'Out-of-court or case settlement payout',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'LegalFeePayment', 
                'Name'=>'Legal Fee Payment', 
                'Description'=>'Fee payments to external counsel or court',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'PremiumCollection', 
                'Name'=>'Premium Collection', 
                'Description'=>'Customer policy premium received',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'CommissionPayout', 
                'Name'=>'Commission Payout', 
                'Description'=>'Fleet maintenance or repair expense',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'JournalEntryManual', 
                'Name'=>'Manual Journal Entry', 
                'Description'=>'Manually entered financial journal',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
            [
                'Code'=>'VoucherPosting', 
                'Name'=>'Voucher Posting', 
                'Description'=>'Payables voucher (approved invoice)',
                'IsActive'=>1, 
                'CreatedBy' => 1,
                'CreatedOn' => $now,
                'ModifiedBy' => 1,
                'ModifiedOn' => $now,
                'DeletedBy' => null,
                'DeletedOn' => null
            ],
        ]);
    }
}
