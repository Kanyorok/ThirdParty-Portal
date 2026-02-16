<?php

namespace App\Services\Finance;

use App\Enums\Core\ModulesEnum;
use App\Models\Finance\CustomerWallet;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceCreditMovement;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceReceipt;
use App\Models\Finance\FinanceReceiptAllocation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReceiptPostingService
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Create a new receipt with allocations
     */
    public function createReceipt(array $data, array $allocations, UploadedFile $attachment = null): FinanceReceipt
    {
        return DB::transaction(function () use ($data, $allocations, $attachment) {
            // Create the receipt
            $receipt = FinanceReceipt::create([
                'CustomerID' => $data['CustomerID'],
                'ReceiptDate' => $data['ReceiptDate'] ?? now()->toDateString(),
                'AmountReceived' => $data['AmountReceived'],
                'PaymentMethod' => $data['PaymentMethod'],
                'ReferenceNumber' => $data['ReferenceNumber'],
                'ValueDate' => $data['ValueDate'],
                'PostingDate' => $data['PostingDate'],
                'Remarks' => $data['Remarks'],
                'Status' => 'Draft',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Handle file attachment
            if ($attachment) {
                $receipt->newDocument(
                    ModulesEnum::Finance,
                    $attachment,
                    ['FinanceReceiptView'],
                    Auth::user()
                );
            }

            // Process allocations
            $totalAllocated = $this->processAllocations($receipt, $allocations);

            // Handle excess amount (wallet deposit)
            $excessAmount = $data['AmountReceived'] - $totalAllocated;
            if ($excessAmount > 0) {
                $this->handleExcessAmount($receipt, $excessAmount);
            }

            // Log activity
            activity('Receipt Creation')
                ->performedOn($receipt)
                ->causedBy(Auth::user())
                ->withProperties(['receipt_number' => $receipt->ReceiptNumber])
                ->log("Created receipt {$receipt->ReceiptNumber} for customer ID {$receipt->CustomerID}");

            return $receipt->load(['allocations.invoice', 'customer']);
        });
    }

    /**
     * Process receipt allocations to invoices
     */
    protected function processAllocations(FinanceReceipt $receipt, array $allocations): float
    {
        $totalAllocated = 0;

        foreach ($allocations as $allocation) {
            $invoice = FinanceInvoice::findOrFail($allocation['invoice_id']);
            $allocateAmount = min($allocation['allocate'], $invoice->TotalAmount - $invoice->AmountPaid);

            if ($allocateAmount <= 0) {
                continue;
            }

            // Create allocation record
            FinanceReceiptAllocation::create([
                'ReceiptID' => $receipt->Id,
                'InvoiceID' => $invoice->Id,
                'AmountAllocated' => $allocateAmount,
                'AllocationNotes' => "Payment allocation from receipt {$receipt->ReceiptNumber}",
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Update invoice paid amount
            $newAmountPaid = $invoice->AmountPaid + $allocateAmount;
            $invoice->update([
                'AmountPaid' => $newAmountPaid,
                'IsPaid' => $newAmountPaid >= $invoice->TotalAmount,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Create credit movement if customer has credit
            $this->createCreditMovementForPayment($receipt->CustomerID, $allocateAmount, $invoice, $receipt);

            $totalAllocated += $allocateAmount;
        }

        return $totalAllocated;
    }

    /**
     * Handle excess amount by depositing to customer wallet
     */
    protected function handleExcessAmount(FinanceReceipt $receipt, float $excessAmount): void
    {
        $wallet = CustomerWallet::getOrCreateWallet($receipt->CustomerID);

        $wallet->addFunds(
            $excessAmount,
            "Excess payment from receipt {$receipt->ReceiptNumber}",
            'receipt',
            $receipt->Id
        );

        // Update receipt with unapplied amount
        $receipt->update([
            'UnappliedAmount' => $excessAmount,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
    }

    /**
     * Create credit movement for payment received
     */
    protected function createCreditMovementForPayment(int $customerId, float $amount, FinanceInvoice $invoice, FinanceReceipt $receipt): void
    {
        // Check if customer has an active credit profile
        $creditProfile = FinanceCreditManagement::where('CustomerID', $customerId)
            ->where('Status', 'Approved')
            ->where(function ($query) {
                $query->where('ExpiryDate', '>=', now()->toDateString())
                    ->orWhereNull('ExpiryDate');
            })
            ->first();

        if (! $creditProfile) {
            return; // No active credit profile
        }

        // Create payment received movement
        FinanceCreditMovement::create([
            'CreditID' => $creditProfile->Id,
            'CustomerID' => $customerId,
            'MovementType' => 'payment_received',
            'Amount' => $amount, // Positive because payment restores available credit
            'ReferenceType' => 'receipt',
            'ReferenceID' => $receipt->Id,
            'Notes' => "Payment received KSh " . number_format($amount, 2) . " for invoice {$invoice->InvoiceNumber} via receipt {$receipt->ReceiptNumber}",
            'EffectiveOn' => $receipt->ReceiptDate,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
    }

    /**
     * Auto-allocate receipt amount to oldest invoices
     */
    public function autoAllocateReceipt(int $customerId, float $amount, bool $useWallet = false): array
    {
        $allocations = [];
        $remainingAmount = $amount;

        // Include wallet balance if requested
        if ($useWallet) {
            $wallet = CustomerWallet::where('CustomerID', $customerId)->active()->first();
            if ($wallet && $wallet->Balance > 0) {
                $remainingAmount += $wallet->Balance;
            }
        }

        // Get unpaid invoices ordered by oldest first
        $unpaidInvoices = FinanceInvoice::where('CustomerID', $customerId)
            ->where('ApprovalStatus', 'posted')
            ->where('IsPaid', false)
            ->whereColumn('TotalAmount', '>', 'AmountPaid')
            ->orderBy('InvoiceDate')
            ->orderBy('Id')
            ->get();

        foreach ($unpaidInvoices as $invoice) {
            if ($remainingAmount <= 0) {
                break;
            }

            $outstanding = $invoice->TotalAmount - $invoice->AmountPaid;
            $allocateAmount = min($outstanding, $remainingAmount);

            $allocations[] = [
                'invoice_id' => $invoice->Id,
                'allocate' => $allocateAmount,
                'invoice_number' => $invoice->InvoiceNumber,
                'outstanding' => $outstanding,
            ];

            $remainingAmount -= $allocateAmount;
        }

        return [
            'allocations' => $allocations,
            'remaining_amount' => $remainingAmount,
            'total_allocated' => $amount - $remainingAmount + ($useWallet ? ($wallet->Balance ?? 0) : 0),
        ];
    }

    /**
     * Post receipt to GL
     */
    public function postReceiptToGL(FinanceReceipt $receipt): array
    {
        try {
            // Prepare GL entries for receipt
            $glEntries = $this->prepareReceiptGLEntries($receipt);

            // Post to GL using existing transaction service
            $result = $this->transactionService->postTransactions($glEntries);

            if ($result['status'] === 'success') {
                $receipt->update([
                    'Status' => 'Posted',
                    'ApprovalReason' => 'Receipt posted to GL',
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            Log::error('Failed to post receipt to GL', [
                'receipt_id' => $receipt->Id,
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'message' => 'Failed to post receipt to GL: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare GL entries for receipt posting
     */
    protected function prepareReceiptGLEntries(FinanceReceipt $receipt): array
    {
        $entries = [];

        // Debit: Cash/Bank Account (based on payment method)
        $entries[] = [
            'TransactionDate' => $receipt->PostingDate,
            'ReferenceNumber' => $receipt->ReceiptNumber,
            'TransactionType' => 'Receipt',
            'ModuleID' => 1100000, // Finance module
            'SourceTable' => 't_FinanceReceipts',
            'GLAccountID' => $this->getPaymentMethodGLAccount($receipt->PaymentMethod),
            'BranchID' => session('LoginBranchId', 1),
            'DepartmentID' => 1, // Default department
            'DRCR' => 'DR',
            'Amount' => -$receipt->AmountReceived, // Negative for debit
            'CurrencyID' => 1,
            'CurrencyCode' => 'KES',
            'ExchangeRate' => 1,
            'Narration' => "Receipt {$receipt->ReceiptNumber} - {$receipt->PaymentMethod}",
            'SystemDescription' => "Receipt {$receipt->ReceiptNumber} from {$receipt->customer->ThirdPartyName}",
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ];

        // Credit: Accounts Receivable
        $entries[] = [
            'TransactionDate' => $receipt->PostingDate,
            'ReferenceNumber' => $receipt->ReceiptNumber,
            'TransactionType' => 'Receipt',
            'ModuleID' => 1100000,
            'SourceTable' => 't_FinanceReceipts',
            'GLAccountID' => $this->getAccountsReceivableGLAccount(),
            'BranchID' => session('LoginBranchId', 1),
            'DepartmentID' => 1,
            'DRCR' => 'CR',
            'Amount' => $receipt->AmountReceived,
            'CurrencyID' => 1,
            'CurrencyCode' => 'KES',
            'ExchangeRate' => 1,
            'Narration' => "Receipt {$receipt->ReceiptNumber} - Payment received",
            'SystemDescription' => "Receipt {$receipt->ReceiptNumber} from {$receipt->customer->ThirdPartyName}",
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedOn' => now(),
        ];

        return $entries;
    }

    /**
     * Get GL account for payment method
     */
    protected function getPaymentMethodGLAccount(string $paymentMethod): int
    {
        // This would typically come from a mapping table
        // For now, return default cash account
        return match (strtolower($paymentMethod)) {
            'cash' => 1001, // Cash account
            'bank', 'bank_transfer' => 1002, // Bank account
            'mpesa', 'm-pesa' => 1003, // Mobile money account
            'cheque' => 1002, // Bank account
            default => 1001 // Default to cash
        };
    }

    /**
     * Get Accounts Receivable GL account
     */
    protected function getAccountsReceivableGLAccount(): int
    {
        return 1201; // Accounts Receivable account
    }
}
