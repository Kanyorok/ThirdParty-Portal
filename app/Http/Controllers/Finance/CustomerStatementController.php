<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $search = trim((string) $request->get('q', ''));
        $page = max(1, (int) $request->get('page', 1));
        $perPage = 10;

        $this->safeLog('info', 'Select2 ThirdParties lookup called', [
            'q' => $search,
            'page' => $page,
            'perPage' => $perPage,
        ]);

        if (strlen($search) < 2) {
            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
            ]);
        }

        try {
            // Do not restrict columns with select() to avoid breaking traits/relations
            $query = ThirdParties::query()
                ->whereNull('DeletedOn');

            // STRICT FILTER: Only show Customers, Clients, or Tenants
            // Exclude pure Suppliers
            $query->whereHas('types', function ($q) {
                // Check for specific PartyTypes or Codes indicating a customer-like entity
                $q->whereIn('t_ThirdPartyType_ThirdParties.PartyType', [
                    'BancassuranceCustomer',
                    'App\Models\Insurance\BancassuranceCustomer',
                    'PropertyNewTenant',
                    'App\Models\PropertyManagement\PropertyNewTenant',
                    'Client',
                    'TenantMaintenanceId', // Found via invoice analysis
                    // Add any other specific customer MorphClasses here
                ])
                ->orWhere('Code', 'like', 'CU%') // Customers
                ->orWhere('Code', 'like', 'TN%') // Tenants
                ->orWhere('Description', 'Client')
                ->orWhere('Description', 'Tenant');
            });

            // Search filter
            if (! empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('ThirdPartyName', 'like', "%{$search}%")
                      ->orWhere('TradingName', 'like', "%{$search}%")
                      ->orWhere('RegistrationNumber', 'like', "%{$search}%")
                      ->orWhere('TaxPIN', 'like', "%{$search}%")
                      ->orWhere('Email', 'like', "%{$search}%")
                      ->orWhere('Phone', 'like', "%{$search}%");
                });
            }

            $total = $query->count();
            $results = $query->orderBy('ThirdPartyName')
                ->orderBy('TradingName')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->get();

            $items = $results->map(function ($item) {
                $displayName = $item->ThirdPartyName ?: $item->TradingName;
                $idNumber = $item->RegistrationNumber ?: $item->TaxPIN;
                if ($idNumber) {
                    $displayName .= " ({$idNumber})";
                }

                return [
                    'id' => $item->Id,
                    'text' => $displayName,
                ];
            })->values();

            return response()->json([
                'results' => $items,
                'pagination' => [
                    'more' => ($page * $perPage) < $total,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->safeLog('error', 'Select2 ThirdParties lookup failed', [
                'q' => $search,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'results' => [],
                'pagination' => ['more' => false],
                'error' => config('app.debug') ? $e->getMessage() : 'Lookup failed',
            ], 500);
        }
    }

    private function safeLog(string $level, string $message, array $context = []): void
    {
        try {
            Log::log($level, $message, $context);
        } catch (\Throwable $e) {
            // Swallow logging errors to avoid breaking API responses.
        }
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
                ->select('Id', 'ThirdPartyName', 'TradingName', 'Email', 'Phone', 'PhysicalAddress', 'RegistrationNumber')
                ->where('Id', $thirdPartyId)
                ->whereNull('DeletedOn')
                ->first();

            if (! $customerData) {
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
                'id_number' => $customerData->RegistrationNumber ?: 'N/A',
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
                'error' => 'Failed to fetch statement: ' . $e->getMessage(),
            ], 500);
        }
    }
}
