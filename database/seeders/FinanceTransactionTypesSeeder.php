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

        $transactionTypes = [
            ['Id' => 1, 'Code' => 'GoodsReceipt', 'Name' => 'Goods Receipt', 'Description' => 'Records the receipt of goods into inventory, typically from a purchase order or supplier delivery.'],
            ['Id' => 2, 'Code' => 'ServiceReceipt', 'Name' => 'Service Receipt', 'Description' => 'Documents the receipt of non-stock items or services, such as consulting or maintenance, often linked to a goods receipt note (GRN).'],
            ['Id' => 3, 'Code' => 'InvoiceBooking', 'Name' => 'Invoice Booking', 'Description' => 'Records a supplier invoice, either matched to a goods receipt note or booked directly for payment processing.'],
            ['Id' => 4, 'Code' => 'PurchaseReturn', 'Name' => 'Purchase Return', 'Description' => 'Tracks the return of purchased goods to a supplier, often resulting in a credit note or refund.'],
            ['Id' => 5, 'Code' => 'StockIssue', 'Name' => 'Stock Issue', 'Description' => 'Records the issuance of stock items from inventory for internal use or consumption.'],
            ['Id' => 6, 'Code' => 'StockAdjustment', 'Name' => 'Stock Adjustment', 'Description' => 'Adjusts inventory levels to account for write-offs, shrinkage, or discrepancies identified during stock counts.'],
            ['Id' => 7, 'Code' => 'RentInvoicing', 'Name' => 'Rent Invoicing', 'Description' => 'Generates an invoice for rental charges owed by a tenant for property occupancy.'],
            ['Id' => 8, 'Code' => 'PropertyReceipts', 'Name' => 'Property Receipts', 'Description' => 'Records payments received from tenants for rent, utilities, or other property-related charges.'],
            ['Id' => 9, 'Code' => 'FuelIssue', 'Name' => 'Fuel Issue', 'Description' => 'Tracks the issuance of fuel for vehicles or equipment, often for operational use.'],
            ['Id' => 10, 'Code' => 'MaintenanceCost', 'Name' => 'Maintenance Cost', 'Description' => 'Records expenses incurred for fleet or equipment maintenance, such as repairs or servicing.'],
            ['Id' => 11, 'Code' => 'SettlementPayment', 'Name' => 'Settlement Payment', 'Description' => 'Documents payments made for out-of-court settlements or legal case resolutions.'],
            ['Id' => 12, 'Code' => 'LegalFeePayment', 'Name' => 'Legal Fee Payment', 'Description' => 'Tracks payments to external legal counsel or court fees for legal services.'],
            ['Id' => 13, 'Code' => 'PremiumCollection', 'Name' => 'Premium Collection', 'Description' => 'Records premiums received from customers for insurance policies or similar financial products.'],
            ['Id' => 14, 'Code' => 'CommissionPayout', 'Name' => 'Commission Payout', 'Description' => 'Records payments of commissions to agents, brokers, or sales representatives for services rendered.'],
            ['Id' => 15, 'Code' => 'AccountsPayableInvoice', 'Name' => 'Accounts Payable Invoice', 'Description' => 'Tracks invoices received from suppliers, representing amounts owed for goods or services.'],
            ['Id' => 16, 'Code' => 'AccountsReceivableInvoice', 'Name' => 'Accounts Receivable Invoice', 'Description' => 'Records invoices issued to customers, representing amounts owed for goods or services provided.'],
            ['Id' => 17, 'Code' => 'VoucherPosting', 'Name' => 'Voucher Posting', 'Description' => 'Processes approved payables vouchers, typically linked to invoices ready for payment.'],
            ['Id' => 18, 'Code' => 'CreditNote', 'Name' => 'Credit Note', 'Description' => 'Issues a credit note to a customer or supplier, often to offset an overpayment or return.'],
            ['Id' => 19, 'Code' => 'DebitNote', 'Name' => 'Debit Note', 'Description' => 'Issues a debit note to a customer or supplier, typically to request additional payment for under-billed amounts.'],
            ['Id' => 20, 'Code' => 'Receipts', 'Name' => 'Receipts', 'Description' => 'Records approved payments received, such as customer payments for invoices or other dues.'],
            ['Id' => 21, 'Code' => 'CreditManagement', 'Name' => 'Credit Management', 'Description' => 'Manages approved credit transactions, such as credit limits or payment terms for customers.'],
            ['Id' => 22, 'Code' => 'Interbranch Transfers', 'Name' => 'Interbranch Transfers', 'Description' => 'Manages approved interbranch transfers.'],
        ];

        foreach ($transactionTypes as $type) {
            $exists = DB::table('t_FinanceTransactionTypes')->where('Code', $type['Code'])->exists();

            if (!$exists) {
                DB::table('t_FinanceTransactionTypes')->insert([
                    'Code' => $type['Code'],
                    'Name' => $type['Name'],
                    'Description' => $type['Description'],
                    'IsActive' => 1,
                    'CreatedBy' => 1,
                    'CreatedOn' => $now,
                    'ModifiedBy' => 1,
                    'ModifiedOn' => $now,
                    'DeletedBy' => null,
                    'DeletedOn' => null
                ]);
            }
        }
    }
}
