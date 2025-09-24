<?php

namespace App\Services\Finance;

use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceCreditMovement;
use App\Models\Finance\FinanceInvoice;
use App\Models\ThirdParty\ThirdParties;
use Illuminate\Support\Facades\DB;

class CreditCalculationService
{
    /**
     * Calculate credit utilization for a customer
     *
     * @param int $customerId Customer ID
     * @return array ['credit_limit' => float, 'used' => float, 'available' => float, 'utilization' => float, 'has_credit' => bool]
     */
    public function calculateCustomerCreditUtilization(int $customerId): array
    {
        // Find active credit profile for customer
        $credit = FinanceCreditManagement::where('CustomerID', $customerId)
            ->whereRaw('LOWER(Status) = ?', ['approved'])
            ->where(function($query) {
                $query->where('ExpiryDate', '>=', now()->toDateString())
                      ->orWhereNull('ExpiryDate');
            })
            ->first();

        if (!$credit) {
            return [
                'credit_limit' => 0.0,
                'used' => 0.0,
                'available' => 0.0,
                'utilization' => 0.0,
                'has_credit' => false,
                'credit_id' => null,
                'effective_from' => null,
                'expiry_date' => null,
                'payment_terms' => null
            ];
        }

        // Calculate actual usage from negative movements (decreases in credit)
        $totalUsage = FinanceCreditMovement::where('CreditID', $credit->Id)
            ->where('CustomerID', $customerId)
            ->where('Amount', '<', 0) // Only negative amounts (usage)
            ->sum('Amount');

        // Convert to positive value for "used" amount
        $used = abs((float)$totalUsage);

        // Debug logging - remove after testing
        \Log::info('Credit calculation debug', [
            'credit_id' => $credit->Id,
            'customer_id' => $customerId,
            'total_usage_raw' => $totalUsage,
            'used_calculated' => $used
        ]);

        // If no credit movements exist, fall back to invoice-based calculation
        $movementCount = FinanceCreditMovement::where('CreditID', $credit->Id)
            ->where('CustomerID', $customerId)
            ->count();
            
        if ($movementCount == 0) {
            $used = $this->calculateUsedFromInvoices($customerId, $credit->EffectiveFrom);
        }

        // Calculate available credit using net balance approach
        $netCreditBalance = FinanceCreditMovement::where('CreditID', $credit->Id)
            ->where('CustomerID', $customerId)
            ->sum('Amount');

        $limit = (float)($credit->CreditLimit ?? 0);
        $available = max(0.0, (float)$netCreditBalance);
        $utilization = $limit > 0 ? min(100, round(($used / $limit) * 100, 2)) : 0.0;

        // Debug logging - remove after testing
        \Log::info('Credit balance debug', [
            'credit_id' => $credit->Id,
            'customer_id' => $customerId,
            'net_balance' => $netCreditBalance,
            'available_calculated' => $available,
            'credit_limit' => $limit,
            'utilization' => $utilization
        ]);

        return [
            'credit_limit' => $limit,
            'used' => $used,
            'available' => $available,
            'utilization' => $utilization,
            'has_credit' => true,
            'credit_id' => $credit->Id,
            'effective_from' => $credit->EffectiveFrom,
            'expiry_date' => $credit->ExpiryDate,
            'payment_terms' => $credit->PaymentTerms
        ];
    }

    /**
     * Check if customer can apply credit for a specific amount
     *
     * @param int $customerId Customer ID
     * @param float $amount Amount to check
     * @return array ['can_apply' => bool, 'available' => float, 'message' => string]
     */
    public function canApplyCredit(int $customerId, float $amount): array
    {
        $utilization = $this->calculateCustomerCreditUtilization($customerId);

        if (!$utilization['has_credit']) {
            return [
                'available' => false,
                'can_apply' => false,
                'available_amount' => 0.0,
                'message' => 'Customer has no active credit profile.'
            ];
        }

        if ($amount > $utilization['available']) {
            return [
                'available' => false,
                'can_apply' => false,
                'available_amount' => $utilization['available'],
                'message' => "Insufficient credit. Available: KSh " . number_format($utilization['available'], 2) .
                           ", Required: KSh " . number_format($amount, 2)
            ];
        }

        return [
            'available' => true,
            'can_apply' => true,
            'available_amount' => $utilization['available'],
            'credit_limit' => $utilization['credit_limit'],
            'message' => 'Credit available',
            'remaining_after' => $utilization['available'] - $amount
        ];
    }

    /**
     * Get customer credit summary for display
     *
     * @param int $customerId Customer ID
     * @return array Credit summary with formatted values
     */
    public function getCustomerCreditSummary(int $customerId): array
    {
        $utilization = $this->calculateCustomerCreditUtilization($customerId);

        return [
            'has_credit' => $utilization['has_credit'],
            'credit_limit' => $utilization['credit_limit'],
            'used' => $utilization['used'],
            'available' => $utilization['available'],
            'utilization_percentage' => $utilization['utilization'],
            'credit_limit_formatted' => number_format($utilization['credit_limit'], 2),
            'used_formatted' => number_format($utilization['used'], 2),
            'available_formatted' => number_format($utilization['available'], 2),
            'utilization_badge_class' => $this->getUtilizationBadgeClass($utilization['utilization']),
            'effective_from' => $utilization['effective_from'],
            'expiry_date' => $utilization['expiry_date'],
            'payment_terms' => $utilization['payment_terms']
        ];
    }

    /**
     * Calculate used credit from invoices (fallback method)
     *
     * @param int $customerId Customer ID
     * @param string|null $effectiveFrom Credit effective date
     * @return float Used amount
     */
    private function calculateUsedFromInvoices(int $customerId, string $effectiveFrom = null): float
    {
        $customer = ThirdParties::find($customerId);
        $customerName = $customer ? $customer->ThirdPartyName : null;

        $query = FinanceInvoice::where(function($mainQuery) use ($customerId, $customerName) {
            $mainQuery->where('CustomerID', $customerId);

            // Handle customer ID mismatches by searching by name
            if ($customerName) {
                $mainQuery->orWhereHas('customer', function($customerQuery) use ($customerName) {
                    $customerQuery->where('ThirdPartyName', 'like', "%{$customerName}%");
                });
            }
        })
        ->where(function($query) {
            $query->where('ApprovalStatus', 'posted')
                  ->orWhere(function($subQuery) {
                      $subQuery->where('ApprovalStatus', 'draft')
                               ->where('UseCredit', true);
                  });
        });

        // Only include invoices after credit effective date
        if ($effectiveFrom) {
            $query->where('CreatedOn', '>=', $effectiveFrom);
        }

        $used = $query->sum(DB::raw('ISNULL(TotalAmount,0) - ISNULL(AmountPaid,0)'));

        return max(0.0, (float)$used);
    }

    /**
     * Get CSS class for utilization badge based on percentage
     *
     * @param float $utilization Utilization percentage
     * @return string CSS class
     */
    private function getUtilizationBadgeClass(float $utilization): string
    {
        if ($utilization >= 80) {
            return 'bg-danger';
        } elseif ($utilization >= 60) {
            return 'bg-warning';
        } else {
            return 'bg-success';
        }
    }
}
