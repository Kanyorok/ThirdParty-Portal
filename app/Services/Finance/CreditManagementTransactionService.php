<?php

namespace App\Services\Finance;

use App\Models\Finance\FinanceCreditAdjustment;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceCreditMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditManagementTransactionService
{
    protected TransactionService $transactionService;

    // Module and Transaction Type IDs from your seeder
    public const FINANCE_MODULE_ID = 1100000;
    public const CREDIT_MANAGEMENT_TRANSACTION_TYPE = 21; // From your seeder mapping

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Post GL transactions when a credit profile is approved
     */
    public function postCreditApproval(FinanceCreditManagement $credit, string $approvalReason): array
    {
        try {
            $payload = [
                'ModuleID' => self::FINANCE_MODULE_ID,
                'ThirdPartyID' => $credit->CustomerID,
                'TransactionTypeID' => self::CREDIT_MANAGEMENT_TRANSACTION_TYPE,
                'TransactionType' => 'Credit Management - Initial Approval',
                'ReferenceNumber' => "CREDIT-{$credit->Id}",
                'TransactionDate' => $credit->EffectiveFrom ?? now()->toDateString(),
                'Amount' => (float)$credit->CreditLimit,
                'TaxAmount' => 0.00,
                'BranchID' => session('LoginBranchId', 1),
                'DepartmentID' => null,
                'CurrencyID' => 56, // KES
                'CurrencyCode' => 'KES',
                'ExchangeRate' => 1.0,
                'Narration' => "Credit limit approved for {$credit->customer->ThirdPartyName}: {$approvalReason}",
                'SourceTable' => 't_FinanceCreditManagement',
                'SystemDescription' => "Initial credit approval - Profile #{$credit->Id}",
                'IdempotencyKey' => "credit_approval_{$credit->Id}",
            ];

            $result = $this->transactionService->postFromTypeMapping($payload);

            // Log the transaction reference back to credit management
            if ($result['status'] === 'success') {
                activity('Credit Management GL Posting')
                    ->performedOn($credit)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'action' => 'credit_approval_posted',
                        'transaction_batch' => $result['batch_number'] ?? null,
                        'amount' => $credit->CreditLimit,
                    ])
                    ->log("Posted GL transactions for credit approval #{$credit->Id}");
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Credit approval GL posting failed', [
                'credit_id' => $credit->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception("Failed to post GL transactions for credit approval: " . $e->getMessage());
        }
    }

    /**
     * Post GL transactions when a credit adjustment is approved
     */
    public function postCreditAdjustment(FinanceCreditAdjustment $adjustment): array
    {
        try {
            $transactionType = match ($adjustment->AdjustmentType) {
                'increase' => 'Credit Management - Credit Increase',
                'decrease' => 'Credit Management - Credit Decrease',
                'revision' => 'Credit Management - Credit Revision',
                default => 'Credit Management - Adjustment'
            };

            $payload = [
                'ModuleID' => self::FINANCE_MODULE_ID,
                'ThirdPartyID' => $adjustment->CustomerID,
                'TransactionTypeID' => self::CREDIT_MANAGEMENT_TRANSACTION_TYPE,
                'TransactionType' => $transactionType,
                'ReferenceNumber' => "CREDIT-ADJ-{$adjustment->Id}",
                'TransactionDate' => $adjustment->EffectiveFrom->toDateString(),
                'Amount' => (float)$adjustment->Amount,
                'TaxAmount' => 0.00,
                'BranchID' => session('LoginBranchId', 1),
                'DepartmentID' => null,
                'CurrencyID' => 56, // KES
                'CurrencyCode' => 'KES',
                'ExchangeRate' => 1.0,
                'Narration' => "Credit {$adjustment->AdjustmentType} for {$adjustment->customer->ThirdPartyName}: {$adjustment->Reason}",
                'SourceTable' => 't_FinanceCreditAdjustments',
                'SystemDescription' => "Credit {$adjustment->AdjustmentType} - Adjustment #{$adjustment->Id}",
                'IdempotencyKey' => "credit_adjustment_{$adjustment->Id}",
            ];

            $result = $this->transactionService->postFromTypeMapping($payload);

            // Log the transaction reference back to adjustment
            if ($result['status'] === 'success') {
                activity('Credit Management GL Posting')
                    ->performedOn($adjustment)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'action' => 'credit_adjustment_posted',
                        'adjustment_type' => $adjustment->AdjustmentType,
                        'transaction_batch' => $result['batch_number'] ?? null,
                        'amount' => $adjustment->Amount,
                    ])
                    ->log("Posted GL transactions for credit adjustment #{$adjustment->Id}");
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Credit adjustment GL posting failed', [
                'adjustment_id' => $adjustment->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception("Failed to post GL transactions for credit adjustment: " . $e->getMessage());
        }
    }

    /**
     * Post GL transactions for credit utilization (when customer uses credit)
     */
    public function postCreditUtilization(FinanceCreditManagement $credit, float $amount, string $referenceNumber, string $description): array
    {
        try {
            $payload = [
                'ModuleID' => self::FINANCE_MODULE_ID,
                'ThirdPartyID' => $credit->CustomerID,
                'TransactionTypeID' => self::CREDIT_MANAGEMENT_TRANSACTION_TYPE,
                'TransactionType' => 'Credit Management - Credit Utilization',
                'ReferenceNumber' => $referenceNumber,
                'TransactionDate' => now()->toDateString(),
                'Amount' => (float)$amount,
                'TaxAmount' => 0.00,
                'BranchID' => session('LoginBranchId', 1),
                'DepartmentID' => null,
                'CurrencyID' => 56, // KES
                'CurrencyCode' => 'KES',
                'ExchangeRate' => 1.0,
                'Narration' => "Credit utilization - {$credit->customer->ThirdPartyName}: {$description}",
                'SourceTable' => 't_FinanceCreditManagement',
                'SystemDescription' => "Credit utilization - {$referenceNumber}",
                'IdempotencyKey' => "credit_utilization_{$credit->Id}_{$referenceNumber}",
            ];

            $result = $this->transactionService->postFromTypeMapping($payload);

            // Create movement record
            if ($result['status'] === 'success') {
                FinanceCreditMovement::create([
                    'CreditID' => $credit->Id,
                    'CustomerID' => $credit->CustomerID,
                    'MovementType' => 'usage',
                    'Amount' => -$amount, // Negative because usage decreases available credit
                    'ReferenceType' => 'credit_utilization',
                    'ReferenceID' => null,
                    'Notes' => "Credit utilization: {$description}",
                    'EffectiveOn' => now(),
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                activity('Credit Management GL Posting')
                    ->performedOn($credit)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'action' => 'credit_utilization_posted',
                        'transaction_batch' => $result['batch_number'] ?? null,
                        'amount' => $amount,
                        'reference' => $referenceNumber,
                    ])
                    ->log("Posted GL transactions for credit utilization #{$referenceNumber}");
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Credit utilization GL posting failed', [
                'credit_id' => $credit->Id,
                'amount' => $amount,
                'reference' => $referenceNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception("Failed to post GL transactions for credit utilization: " . $e->getMessage());
        }
    }

    /**
     * Post GL transactions for credit payments (when customer pays back credit)
     */
    public function postCreditPayment(FinanceCreditManagement $credit, float $amount, string $referenceNumber, string $description): array
    {
        try {
            $payload = [
                'ModuleID' => self::FINANCE_MODULE_ID,
                'ThirdPartyID' => $credit->CustomerID,
                'TransactionTypeID' => self::CREDIT_MANAGEMENT_TRANSACTION_TYPE,
                'TransactionType' => 'Credit Management - Credit Payment',
                'ReferenceNumber' => $referenceNumber,
                'TransactionDate' => now()->toDateString(),
                'Amount' => (float)$amount,
                'TaxAmount' => 0.00,
                'BranchID' => session('LoginBranchId', 1),
                'DepartmentID' => null,
                'CurrencyID' => 56, // KES
                'CurrencyCode' => 'KES',
                'ExchangeRate' => 1.0,
                'Narration' => "Credit payment - {$credit->customer->ThirdPartyName}: {$description}",
                'SourceTable' => 't_FinanceCreditManagement',
                'SystemDescription' => "Credit payment - {$referenceNumber}",
                'IdempotencyKey' => "credit_payment_{$credit->Id}_{$referenceNumber}",
            ];

            $result = $this->transactionService->postFromTypeMapping($payload);

            // Create movement record
            if ($result['status'] === 'success') {
                FinanceCreditMovement::create([
                    'CreditID' => $credit->Id,
                    'CustomerID' => $credit->CustomerID,
                    'MovementType' => 'payment',
                    'Amount' => $amount, // Positive because payment increases available credit
                    'ReferenceType' => 'credit_payment',
                    'ReferenceID' => null,
                    'Notes' => "Credit payment: {$description}",
                    'EffectiveOn' => now(),
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                activity('Credit Management GL Posting')
                    ->performedOn($credit)
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'action' => 'credit_payment_posted',
                        'transaction_batch' => $result['batch_number'] ?? null,
                        'amount' => $amount,
                        'reference' => $referenceNumber,
                    ])
                    ->log("Posted GL transactions for credit payment #{$referenceNumber}");
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Credit payment GL posting failed', [
                'credit_id' => $credit->Id,
                'amount' => $amount,
                'reference' => $referenceNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception("Failed to post GL transactions for credit payment: " . $e->getMessage());
        }
    }

    /**
     * Reverse GL transactions when credit is cancelled or adjustments are rejected
     */
    public function reverseCreditTransaction(string $originalIdempotencyKey, string $reason): array
    {
        try {
            // Find the original transaction
            $originalTx = DB::table('t_FinanceGLTransactions')
                ->where('IdempotencyKey', $originalIdempotencyKey)
                ->first();

            if (! $originalTx) {
                throw new \Exception("Original transaction not found for reversal");
            }

            $payload = [
                'ModuleID' => self::FINANCE_MODULE_ID,
                'ThirdPartyID' => $originalTx->ThirdPartyID,
                'TransactionTypeID' => self::CREDIT_MANAGEMENT_TRANSACTION_TYPE,
                'TransactionType' => 'Credit Management - Reversal',
                'ReferenceNumber' => "REV-{$originalTx->ReferenceNumber}",
                'TransactionDate' => now()->toDateString(),
                'Amount' => (float)$originalTx->Amount,
                'TaxAmount' => 0.00,
                'BranchID' => session('LoginBranchId', 1),
                'DepartmentID' => null,
                'CurrencyID' => 56, // KES
                'CurrencyCode' => 'KES',
                'ExchangeRate' => 1.0,
                'Narration' => "Reversal: {$reason}",
                'SourceTable' => $originalTx->SourceTable,
                'SystemDescription' => "Reversal of {$originalTx->SystemDescription}",
                'IdempotencyKey' => "reversal_{$originalIdempotencyKey}",
                // Reverse the DR/CR accounts
                'DebitGLAccountID' => $originalTx->CreditGLAccountID,  // Flip
                'CreditGLAccountID' => $originalTx->DebitGLAccountID,   // Flip
            ];

            $result = $this->transactionService->postFromTypeMapping($payload);

            if ($result['status'] === 'success') {
                activity('Credit Management GL Posting')
                    ->causedBy(Auth::user())
                    ->withProperties([
                        'action' => 'credit_transaction_reversed',
                        'original_key' => $originalIdempotencyKey,
                        'reversal_batch' => $result['batch_number'] ?? null,
                        'reason' => $reason,
                    ])
                    ->log("Reversed GL transaction: {$originalIdempotencyKey}");
            }

            return $result;
        } catch (\Throwable $e) {
            Log::error('Credit transaction reversal failed', [
                'original_key' => $originalIdempotencyKey,
                'reason' => $reason,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new \Exception("Failed to reverse GL transaction: " . $e->getMessage());
        }
    }
}
