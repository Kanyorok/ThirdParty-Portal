<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerStatementController extends Controller
{
    /**
     * Display the customer statement selection page
     */
    public function index()
    {
        return view('finance.accountsreceivable.customerstatement.statement');
    }

    /**
     * Select2 API endpoint for searching third parties
     */
    public function select2ThirdParties(Request $request)
    {
        $search = $request->get('q', '');
        $page = $request->get('page', 1);
        $perPage = 10;

        $query = DB::table('t_ThirdParties')
            ->select('Id', 'ThirdPartyName', 'TradingName', 'Email', 'Phone', 'IDNumber')
            ->whereNull('DeletedOn');

        // Search filter
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('ThirdPartyName', 'like', "%{$search}%")
                  ->orWhere('TradingName', 'like', "%{$search}%")
                  ->orWhere('IDNumber', 'like', "%{$search}%")
                  ->orWhere('Email', 'like', "%{$search}%");
            });
        }

        $total = $query->count();
        $results = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $items = $results->map(function($item) {
            $displayName = $item->ThirdPartyName ?: $item->TradingName;
            if ($item->IDNumber) {
                $displayName .= " ({$item->IDNumber})";
            }
            return [
                'id' => $item->Id,
                'text' => $displayName,
            ];
        });

        return response()->json([
            'results' => $items,
            'pagination' => [
                'more' => ($page * $perPage) < $total
            ]
        ]);
    }

    /**
     * Fetch and display customer statement
     */
    public function statement($thirdPartyId)
    {
        //For ERP it will be a detailed statement
        $Type = 'Detailed';
        try {
            // Fetch customer details from t_ThirdParties
            $customerData = DB::table('t_ThirdParties')
                ->select('Id', 'ThirdPartyName', 'TradingName', 'Email', 'Phone', 'PhysicalAddress', 'IDNumber')
                ->where('Id', $thirdPartyId)
                ->whereNull('DeletedOn')
                ->first();

            if (!$customerData) {
                return response()->json(['error' => 'Customer not found'], 404);
            }

            // Get customer types
            $customerTypes = DB::table('t_ThirdPartyType_ThirdParties as ttp')
                ->join('t_ThirdPartyTypes as tt', 'ttp.TypeId', '=', 'tt.TypeId')
                ->where('ttp.ThirdPartyId', $thirdPartyId)
                ->whereNull('ttp.DeletedOn')
                ->pluck('tt.Description')
                ->toArray();

            // Execute stored procedure to get statement add another parameter to get the statement for Detailed or Summary
            $transactions = DB::select('EXEC p_GetClientStatement @ThirdPartyID = ?, @Type = ?', [$thirdPartyId, $Type]);

            // Group transactions by ThirdPartyType
            $tenantTransactions = [];
            $supplierTransactions = [];

            foreach ($transactions as $transaction) {
                $transactionArray = [
                    'reference_number' => $transaction->ReferenceNumber,
                    'transaction_id' => $transaction->TransactionID,
                    'date' => \Carbon\Carbon::parse($transaction->TransactionDate),
                    'description' => $transaction->Description,
                    'transaction_type' => $transaction->TransactionType,
                    'debit' => (float) $transaction->Debit,
                    'credit' => (float) $transaction->Credit,
                    'balance' => (float) $transaction->RunningBalance,
                    'currency_code' => $transaction->CurrencyCode,
                    'source' => $transaction->Source,
                ];

                // Group by ThirdPartyType
                if ($transaction->ThirdPartyType === 'Tenant') {
                    $tenantTransactions[] = $transactionArray;
                } elseif ($transaction->ThirdPartyType === 'Supplier') {
                    $supplierTransactions[] = $transactionArray;
                }
            }

            // Prepare customer data
            $customer = [
                'id' => $customerData->Id,
                'name' => $customerData->ThirdPartyName ?: $customerData->TradingName,
                'id_number' => $customerData->IDNumber ?: 'N/A',
                'email' => $customerData->Email ?: 'N/A',
                'phone' => $customerData->Phone ?: 'N/A',
                'address' => $customerData->PhysicalAddress ?: 'N/A',
                'types' => $customerTypes,
            ];

            return response()->json([
                'success' => true,
                'customer' => $customer,
                'tenantTransactions' => $tenantTransactions,
                'supplierTransactions' => $supplierTransactions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch statement: ' . $e->getMessage()
            ], 500);
        }
    }
}
