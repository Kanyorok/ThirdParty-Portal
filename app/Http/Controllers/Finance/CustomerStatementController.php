<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerStatementController extends Controller
{
    public function index()
    {
        // Dummy data for dashboard cards
        $stats = [
            'total_customers' => 487,
            'erp_customers' => 312,
            'cbs_customers' => 245,
            'both_systems' => 70,
            'sync_operations' => 34,
            'last_sync_date' => now()->subHours(2),
            'sync_status' => 'success',
        ];

        // Dummy data for customers table
        $customers = [
            [
                'id' => 1, 
                'name' => 'John Kamau Mwangi', 
                'id_number' => '12345678',
                'email' => 'john.kamau@example.com',
                'types' => ['Tenant', 'Client'],
                'sources' => 'Both',
                'erp_balance' => 45000.00,
                'cbs_balance' => 125000.00
            ],
            [
                'id' => 2,
                'name' => 'Mary Wanjiku Njeri',
                'id_number' => '23456789',
                'email' => 'mary.wanjiku@example.com',
                'types' => ['Supplier'],
                'sources' => 'ERP',
                'erp_balance' => -87500.00,
                'cbs_balance' => 0
            ],
            [
                'id' => 3,
                'name' => 'Peter Omondi Otieno',
                'id_number' => '34567890',
                'email' => 'peter.omondi@example.com',
                'types' => ['Client'],
                'sources' => 'CBS',
                'erp_balance' => 0,
                'cbs_balance' => 234500.00
            ],
            [
                'id' => 4,
                'name' => 'Grace Akinyi Odhiambo',
                'id_number' => '45678901',
                'email' => 'grace.akinyi@example.com',
                'types' => ['Tenant', 'Supplier', 'Client'],
                'sources' => 'Both',
                'erp_balance' => 156000.00,
                'cbs_balance' => 89000.00
            ],
            [
                'id' => 5,
                'name' => 'James Kipchoge Rotich',
                'id_number' => '56789012',
                'email' => 'james.kipchoge@example.com',
                'types' => ['Tenant'],
                'sources' => 'ERP',
                'erp_balance' => 23000.00,
                'cbs_balance' => 0
            ],
            [
                'id' => 6,
                'name' => 'Sarah Njoki Ndung\'u',
                'id_number' => '67890123',
                'email' => 'sarah.njoki@example.com',
                'types' => ['Client'],
                'sources' => 'CBS',
                'erp_balance' => 0,
                'cbs_balance' => 567800.00
            ],
            [
                'id' => 7,
                'name' => 'David Mutua Kioko',
                'id_number' => '78901234',
                'email' => 'david.mutua@example.com',
                'types' => ['Supplier', 'Client'],
                'sources' => 'Both',
                'erp_balance' => -45600.00,
                'cbs_balance' => 123400.00
            ],
            [
                'id' => 8,
                'name' => 'Catherine Wambui Kariuki',
                'id_number' => '89012345',
                'email' => 'catherine.wambui@example.com',
                'types' => ['Tenant'],
                'sources' => 'ERP',
                'erp_balance' => 78900.00,
                'cbs_balance' => 0
            ],
        ];

        // Dummy sync history data
        $syncHistory = [
            ['id' => 1, 'date' => now()->subHours(2), 'type' => 'Incremental', 'status' => 'Success', 'records_synced' => 89, 'source' => 'ERP & CBS', 'duration' => '4m 23s', 'initiated_by' => 'System'],
            ['id' => 2, 'date' => now()->subDays(1), 'type' => 'Full', 'status' => 'Success', 'records_synced' => 487, 'source' => 'ERP & CBS', 'duration' => '12m 34s', 'initiated_by' => 'Admin User'],
            ['id' => 3, 'date' => now()->subDays(2), 'type' => 'Incremental', 'status' => 'Success', 'records_synced' => 56, 'source' => 'CBS', 'duration' => '3m 12s', 'initiated_by' => 'System'],
            ['id' => 4, 'date' => now()->subDays(3), 'type' => 'Incremental', 'status' => 'Failed', 'records_synced' => 0, 'source' => 'ERP', 'duration' => '0m 45s', 'initiated_by' => 'System'],
            ['id' => 5, 'date' => now()->subDays(4), 'type' => 'Full', 'status' => 'Success', 'records_synced' => 456, 'source' => 'ERP & CBS', 'duration' => '11m 18s', 'initiated_by' => 'Admin User'],
        ];

        
        return view('finance.accountsreceivable.customerstatement.index',compact('stats', 'customers', 'syncHistory'));
        // return view('crdb.customer.index', compact('stats', 'customers', 'syncHistory'));
    }



    public function statement($customerId)
    {
        // Dummy customer statement data
        $customer = [
            'id' => $customerId,
            'name' => 'John Kamau Mwangi',
            'id_number' => '12345678',
            'email' => 'john.kamau@example.com',
            'phone' => '+254 700 123 456',
            'address' => 'P.O Box 12345-00100, Nairobi',
            'types' => ['Tenant', 'Client'],
        ];

        // Tenant transactions (ERP)
        $tenantTransactions = [
            ['date' => now()->subDays(5), 'description' => 'Rent Payment - Property A', 'debit' => 0, 'credit' => 45000.00, 'balance' => 45000.00],
            ['date' => now()->subDays(10), 'description' => 'Water Bill', 'debit' => 2500.00, 'credit' => 0, 'balance' => 42500.00],
            ['date' => now()->subDays(15), 'description' => 'Rent Payment - Property A', 'debit' => 0, 'credit' => 45000.00, 'balance' => 87500.00],
        ];

        // Supplier transactions (ERP)
        $supplierTransactions = [];

        // Client transactions (CBS)
        $clientTransactions = [
            ['date' => now()->subDays(3), 'description' => 'Loan Payment', 'debit' => 0, 'credit' => 25000.00, 'balance' => 125000.00],
            ['date' => now()->subDays(7), 'description' => 'Interest Charge', 'debit' => 5000.00, 'credit' => 0, 'balance' => 100000.00],
            ['date' => now()->subDays(14), 'description' => 'Loan Disbursement', 'debit' => 100000.00, 'credit' => 0, 'balance' => 95000.00],
            ['date' => now()->subDays(21), 'description' => 'Account Opening', 'debit' => 0, 'credit' => 5000.00, 'balance' => 5000.00],
        ];

        // return view('crdb.customer.statement', compact('customer', 'tenantTransactions', 'supplierTransactions', 'clientTransactions'));
    }
}
