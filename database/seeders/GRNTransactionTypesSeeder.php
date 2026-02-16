<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GRNTransactionTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $transactionTypes = [
            [
                'Code' => 'GRN-STOCK',
                'Name' => 'Goods Receipt - Stock Items',
                'Description' => 'Receipt of stock/inventory items that need to be added to stock',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'Code' => 'GRN-ASSET',
                'Name' => 'Goods Receipt - Asset Items',
                'Description' => 'Receipt of asset items that need to be registered in asset register',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'Code' => 'GRN-SERVICE',
                'Name' => 'Goods Receipt - Service Items',
                'Description' => 'Receipt/confirmation of services that need to be expensed directly',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'Code' => 'GRN-ACCRUAL',
                'Name' => 'Goods Receipt - Accrual',
                'Description' => 'GRN posted without invoice to create accruals',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'Code' => 'GRN-RETURN',
                'Name' => 'Goods Return to Supplier',
                'Description' => 'Return of goods to supplier due to quality issues or other reasons',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
        ];

        foreach ($transactionTypes as $type) {
            DB::table('t_FinanceTransactionTypes')->updateOrInsert(
                ['Code' => $type['Code']],
                $type
            );
        }

        // Create GL accounts for GRN processing if they don't exist
        $glAccounts = [
            [
                'GLCode' => '1400',
                'GLName' => 'Inventory - Raw Materials',
                'GLAccountTypeID' => 1, // Asset
                'GLTypeGroupID' => 1, // Current Asset
                'GLSubAccountTypeID' => 1, // Default sub-account type
                'IsPostingAccount' => true,
                'NormalBalance' => 'D',
                'Description' => 'Raw materials inventory account',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'GLCode' => '1450',
                'GLName' => 'Inventory - Finished Goods',
                'GLAccountTypeID' => 1, // Asset
                'GLTypeGroupID' => 1, // Current Asset
                'GLSubAccountTypeID' => 1, // Default sub-account type
                'IsPostingAccount' => true,
                'NormalBalance' => 'D',
                'Description' => 'Finished goods inventory account',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'GLCode' => '1600',
                'GLName' => 'Fixed Assets - Equipment',
                'GLAccountTypeID' => 1, // Asset
                'GLTypeGroupID' => 2, // Fixed Asset
                'GLSubAccountTypeID' => 1, // Default sub-account type
                'IsPostingAccount' => true,
                'NormalBalance' => 'D',
                'Description' => 'Fixed assets - equipment and machinery',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'GLCode' => '2100',
                'GLName' => 'Accounts Payable - Trade',
                'GLAccountTypeID' => 2, // Liability
                'GLTypeGroupID' => 3, // Current Liability
                'GLSubAccountTypeID' => 1, // Default sub-account type
                'IsPostingAccount' => true,
                'NormalBalance' => 'C',
                'Description' => 'Trade accounts payable',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'GLCode' => '2150',
                'GLName' => 'Accrued Expenses - Goods Received',
                'GLAccountTypeID' => 2, // Liability
                'GLTypeGroupID' => 3, // Current Liability
                'GLSubAccountTypeID' => 1, // Default sub-account type
                'IsPostingAccount' => true,
                'NormalBalance' => 'C',
                'Description' => 'Accrued expenses for goods received but not invoiced',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'GLCode' => '5100',
                'GLName' => 'Office Expenses',
                'GLAccountTypeID' => 4, // Expense
                'GLTypeGroupID' => 7, // Operating Expense
                'GLSubAccountTypeID' => 1, // Default sub-account type
                'IsPostingAccount' => true,
                'NormalBalance' => 'D',
                'Description' => 'Office and administrative expenses',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'GLCode' => '5200',
                'GLName' => 'Service Expenses',
                'GLAccountTypeID' => 4, // Expense
                'GLTypeGroupID' => 7, // Operating Expense
                'GLSubAccountTypeID' => 1, // Default sub-account type
                'IsPostingAccount' => true,
                'NormalBalance' => 'D',
                'Description' => 'Service-related expenses',
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
        ];

        foreach ($glAccounts as $account) {
            DB::table('t_FinanceGLAccounts')->updateOrInsert(
                ['GLCode' => $account['GLCode']],
                $account
            );
        }

        // Get the procurement module ID
        $procurementModuleId = DB::table('t_Modules')->where('Name', 'Procurement')->value('ModuleID') ?? 300000;

        // Create GL mappings for GRN transaction types
        $glMappings = [
            [
                'ModuleID' => $procurementModuleId,
                'TransactionTypeID' => DB::table('t_FinanceTransactionTypes')->where('Code', 'GRN-STOCK')->value('Id'),
                'DebitGLAccountID' => DB::table('t_FinanceGLAccounts')->where('GLCode', '1400')->value('Id'),
                'CreditGLAccountID' => DB::table('t_FinanceGLAccounts')->where('GLCode', '2150')->value('Id'),
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'ModuleID' => $procurementModuleId,
                'TransactionTypeID' => DB::table('t_FinanceTransactionTypes')->where('Code', 'GRN-ASSET')->value('Id'),
                'DebitGLAccountID' => DB::table('t_FinanceGLAccounts')->where('GLCode', '1600')->value('Id'),
                'CreditGLAccountID' => DB::table('t_FinanceGLAccounts')->where('GLCode', '2150')->value('Id'),
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
            [
                'ModuleID' => $procurementModuleId,
                'TransactionTypeID' => DB::table('t_FinanceTransactionTypes')->where('Code', 'GRN-SERVICE')->value('Id'),
                'DebitGLAccountID' => DB::table('t_FinanceGLAccounts')->where('GLCode', '5200')->value('Id'),
                'CreditGLAccountID' => DB::table('t_FinanceGLAccounts')->where('GLCode', '2150')->value('Id'),
                'IsActive' => true,
                'CreatedBy' => 1,
                'ModifiedBy' => 1,
                'CreatedOn' => now(),
                'ModifiedOn' => now(),
            ],
        ];

        foreach ($glMappings as $mapping) {
            if ($mapping['TransactionTypeID'] && $mapping['DebitGLAccountID'] && $mapping['CreditGLAccountID']) {
                DB::table('t_FinanceGlTransactionsMapping')->updateOrInsert([
                    'ModuleID' => $mapping['ModuleID'],
                    'TransactionTypeID' => $mapping['TransactionTypeID'],
                ], $mapping);
            }
        }

        $this->command->info('GRN transaction types and GL mappings seeded successfully.');
    }
}
