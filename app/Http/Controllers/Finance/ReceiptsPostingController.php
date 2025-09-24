<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceReceipt;
use App\Models\Finance\FinanceReceiptAllocation;
use App\Models\Finance\CustomerWallet;
use App\Services\Finance\TransactionService;
use App\Models\ThirdParty\ThirdParties;
use App\Models\Core\CodeDetail;
use App\Services\Finance\ReceiptPostingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class ReceiptsPostingController extends Controller
{
    // Removed constructor dependency injection to fix route registration issues

    public function index()
    {
        $receipts = FinanceReceipt::with(['customer', 'allocations'])
            ->orderBy('CreatedOn', 'desc')
            ->paginate(15);

        return view('finance.accountsreceivable.receiptsposting.index', compact('receipts'));
    }

    public function create(){
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')
            ->orderBy('Description')
            ->get(['ID','Value','Description']);
        return view('finance.accountsreceivable.receiptsposting.create', compact('paymentMethods'));
    }

    public function show($id){
        $receipt = FinanceReceipt::with(['customer', 'allocations.invoice', 'documents'])
            ->findOrFail($id);

        return view('finance.accountsreceivable.receiptsposting.show', compact('receipt'));
    }

    /**
     * AJAX: Find customer + pending invoices + wallet balance
     */
    public function findCustomer(Request $request)
    {
        try {
        $request->validate([
            'id_number' => 'required|string|min:2'
        ]);

        $q = trim((string) $request->id_number);
        $customer = ThirdParties::query()
            ->where('RegistrationNumber', $q)
            ->orWhere('TaxPIN', $q)
            ->orWhere('Email', $q)
            ->orWhere('Phone', $q)
            ->orWhere('ThirdPartyName', 'like', "%{$q}%")
            ->first();

        if (!$customer) {
            return response()->json(['error' => 'Customer not found'], 404);
            }
        } catch (\Exception $e) {
            \Log::error('Error in findCustomer: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Search failed: ' . $e->getMessage()], 500);
        }

        try {
            $invoices = FinanceInvoice::where('CustomerID', $customer->Id)
            ->where('IsPaid', false)
            ->where('ApprovalStatus', 'posted')
            ->whereColumn('TotalAmount', '>', 'AmountPaid')
            ->orderBy('InvoiceDate')
            ->get()
            ->map(function ($inv) {
                // Safe date formatting
                $issueDate = null;
                $dueDate = null;

                try {
                    if ($inv->InvoiceDate) {
                        $issueDate = \Carbon\Carbon::parse($inv->InvoiceDate)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Use raw date if parsing fails
                    $issueDate = $inv->InvoiceDate;
                }

                try {
                    if ($inv->DueDate) {
                        $dueDate = \Carbon\Carbon::parse($inv->DueDate)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    // Use raw date if parsing fails
                    $dueDate = $inv->DueDate;
                }

                return [
                    'id'        => $inv->Id,
                    'number'    => $inv->InvoiceNumber,
                    'issue_date'=> $issueDate,
                    'due_date'  => $dueDate,
                    'currency'  => [
                        'code'   => 'KES', // Default currency
                        'symbol' => 'KSh'  // Default symbol
                    ],
                    'total'     => (float) ($inv->TotalAmount ?? 0),
                    'paid'      => (float) ($inv->AmountPaid ?? 0),
                ];
            });

        // Get customer wallet balance
        $walletBalance = 0;
        try {
            $wallet = CustomerWallet::where('CustomerID', $customer->Id)->where('IsActive', true)->first();
            $walletBalance = $wallet ? (float) $wallet->Balance : 0;
        } catch (\Exception $e) {
            // Log error but continue without wallet
            \Log::info('Error retrieving wallet for customer ' . $customer->Id . ': ' . $e->getMessage());
        }

        if ($invoices->isEmpty() && $walletBalance == 0) {
            return response()->json(['error' => 'No invoices found for this customer and no wallet balance'], 404);
        }

        $status = is_object($customer->Status ?? null) && method_exists($customer->Status, 'label')
            ? $customer->Status->label()
            : ((string) ($customer->Status ?? ''));

        return response()->json([
            'customer' => [
                'id'        => $customer->Id,
                'name'      => $customer->ThirdPartyName,
                'id_number' => $customer->RegistrationNumber ?? $customer->TaxPIN ?? $q,
                'email'     => $customer->Email,
                'phone'     => $customer->Phone,
                'status'    => $status ?: '—',
                'currency'  => [
                        'code'   => $invoices->first()['currency']['code'] ?? 'KES',
                        'symbol' => $invoices->first()['currency']['symbol'] ?? 'KSh'
                    ],
                    'wallet_balance' => $walletBalance
            ],
            'invoices' => $invoices
            ]);
        } catch (\Exception $e) {
            \Log::error('Error processing customer data: ' . $e->getMessage(), [
                'customer_id' => $customer->Id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to process customer data: ' . $e->getMessage()], 500);
        }
    }

    /**
     * AJAX: Auto-allocate receipt amount
     */
    public function autoAllocate(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:t_ThirdParties,Id',
            'amount' => 'required|numeric|min:0.01',
            'use_wallet' => 'boolean'
        ]);

        $receiptService = app(ReceiptPostingService::class);
        $result = $receiptService->autoAllocateReceipt(
            $request->customer_id,
            $request->amount,
            $request->boolean('use_wallet', false)
        );

        return response()->json($result);
    }

    /**
     * Store a new receipt
     */
    public function store(Request $request)
    {
        // Debug logging
        Log::info('Receipt creation attempt', [
            'request_data' => $request->all(),
            'user_id' => Auth::id()
        ]);

        // Simplified validation for debugging
        $validated = $request->validate([
            'CustomerId' => 'required|numeric',
            'AmountReceived' => 'required|numeric|min:0.01',
            'PaymentMethod' => 'required|string',
            'ReferenceNo' => 'nullable|string|max:100',
            'ValueDate' => 'required|date',
            'PostingDate' => 'required|date',
            'Remarks' => 'nullable|string|max:1000',
            'Attachment' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png',
            'Allocations' => 'required|string',
            'UseWallet' => 'nullable|in:true,false,1,0',
            'WalletAmount' => 'nullable|numeric|min:0'
        ]);
        
        // Convert UseWallet to boolean
        $validated['UseWallet'] = in_array($validated['UseWallet'] ?? 'false', ['true', '1', 1, true], true);
        $validated['WalletAmount'] = (float)($validated['WalletAmount'] ?? 0);
        
        Log::info('Validation passed completely', ['use_wallet_converted' => $validated['UseWallet']]);

        try {
            Log::info('Receipt validation passed', ['validated_data' => $validated]);

            $allocations = json_decode($validated['Allocations'], true);
            Log::info('Allocations decoded', ['allocations' => $allocations]);

            if (empty($allocations)) {
                Log::error('No allocations provided');
                throw ValidationException::withMessages([
                    'Allocations' => 'At least one invoice allocation is required.'
                ]);
            }

            // Validate allocations
            $totalAllocated = 0;
            foreach ($allocations as $allocation) {
                if (!isset($allocation['invoice_id']) || !isset($allocation['allocate'])) {
                    throw ValidationException::withMessages([
                        'Allocations' => 'Invalid allocation format.'
                    ]);
                }
                $totalAllocated += $allocation['allocate'];
            }

            // Handle wallet usage
            $walletAmount = 0;
            if ($validated['UseWallet'] ?? false) {
                $walletAmount = $validated['WalletAmount'];
                if ($walletAmount > 0) {
                    $wallet = CustomerWallet::where('CustomerID', $validated['CustomerId'])->where('IsActive', true)->first();
                    if ($wallet && $wallet->Balance >= $walletAmount) {
                        // Deduct specified amount from wallet
                        $wallet->deductFunds(
                            $walletAmount,
                            "Applied to receipt payment - {$walletAmount}",
                            'receipt',
                            0 // Will be updated after receipt creation
                        );
                    } else {
                        throw new \Exception('Insufficient wallet balance');
                    }
                }
            }

            // Adjust amount received if using wallet
            $adjustedAmount = $validated['AmountReceived'];
            if ($walletAmount > 0 && $totalAllocated <= $walletAmount) {
                // Fully covered by wallet
                $adjustedAmount = 0;
            } elseif ($walletAmount > 0) {
                // Partially covered by wallet
                $adjustedAmount = $totalAllocated - $walletAmount;
            }

            // Ensure we don't have negative amounts
            $adjustedAmount = max(0, $adjustedAmount);

            DB::beginTransaction();

            // Create receipt directly (bypass service for now)
            Log::info('Creating receipt directly');

            $receipt = FinanceReceipt::create([
                'CustomerID' => $validated['CustomerId'],
                'ReceiptDate' => now()->toDateString(),
                'AmountReceived' => $validated['AmountReceived'],
                'PaymentMethod' => $validated['PaymentMethod'],
                'ReferenceNumber' => $validated['ReferenceNo'],
                'ValueDate' => $validated['ValueDate'],
                'PostingDate' => $validated['PostingDate'],
                'Remarks' => $validated['Remarks'],
                'Status' => 'Draft',
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            Log::info('Receipt created', ['receipt_id' => $receipt->Id, 'receipt_number' => $receipt->ReceiptNumber]);

            // Create allocations
            foreach ($allocations as $allocation) {
                FinanceReceiptAllocation::create([
                    'ReceiptID' => $receipt->Id,
                    'InvoiceID' => $allocation['invoice_id'],
                    'AmountAllocated' => $allocation['allocate'],
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Update invoice payment
                $invoice = FinanceInvoice::find($allocation['invoice_id']);
                if ($invoice) {
                    $invoice->update([
                        'AmountPaid' => ($invoice->AmountPaid ?? 0) + $allocation['allocate'],
                        'IsPaid' => (($invoice->AmountPaid ?? 0) + $allocation['allocate']) >= $invoice->TotalAmount,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
                }
            }

            Log::info('Allocations created successfully');

            // Update wallet transaction reference if wallet was used
            if ($walletAmount > 0) {
                $wallet->transactions()
                    ->where('ReferenceType', 'receipt')
                    ->where('ReferenceID', 0)
                    ->where('Amount', $walletAmount)
                    ->update(['ReferenceID' => $receipt->Id]);
            }

            // Handle overpayment - add excess to wallet
            $excessAmount = $validated['AmountReceived'] - $totalAllocated;
            if ($excessAmount > 0) {
                // Get or create wallet for this customer
                $customerWallet = CustomerWallet::getOrCreateWallet($validated['CustomerId']);

                // Add excess amount to wallet
                $customerWallet->addFunds(
                    $excessAmount,
                    "Excess amount from receipt {$receipt->ReceiptNumber}",
                    'receipt',
                    $receipt->Id
                );

                // Update receipt with unapplied amount
                $receipt->update([
                    'UnappliedAmount' => $excessAmount,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now()
                ]);
            }

            DB::commit();

            activity('Receipt Creation')
                ->performedOn($receipt)
                ->causedBy(Auth::user())
                ->withProperties(['total_allocated' => $totalAllocated, 'wallet_used' => $walletAmount])
                ->log("Created receipt {$receipt->ReceiptNumber}");

            return redirect()->route('receiptsposting.show', $receipt->Id)
                ->with('success', "Receipt {$receipt->ReceiptNumber} created successfully.");

        }catch(\Throwable $tt){
            return $tt->getMessage();
        }
        catch (ValidationException $e) {
            Log::error('Receipt validation failed', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Receipt creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $validated['CustomerId'] ?? null,
                'amount' => $validated['AmountReceived'] ?? null,
                'request_data' => $request->all()
            ]);

            return back()->withErrors(['error' => 'Receipt creation failed: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Post receipt to GL
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'action_type' => 'required|in:approve',
            'Reason' => 'required|string|max:1000',
        ]);

        $receipt = FinanceReceipt::findOrFail($id);

        if ($receipt->Status !== 'Draft') {
            return back()->with('error', 'Only draft receipts can be posted.');
        }

        try {
            DB::beginTransaction();
            
            // Process each allocation to handle credit restoration
            foreach ($receipt->allocations as $allocation) {
                $invoice = $allocation->invoice;
                
                // Check if this invoice had credit applied
                if ($invoice->UseCredit) {
                    // Restore credit by creating a credit movement
                    $this->restoreCreditFromPayment($invoice, $allocation->AmountAllocated);
                    Log::info("Credit restored for invoice {$invoice->InvoiceNumber}", [
                        'amount' => $allocation->AmountAllocated,
                        'customer_id' => $invoice->CustomerID
                    ]);
                }
            }
            
            // Calculate total allocated amount (what actually went to invoices)
            $totalAllocated = $receipt->allocations->sum('AmountAllocated');
            
            // Post only allocated amount to GL (not wallet deposits)
            $this->postReceiptToGL($receipt, $validated['Reason'], $totalAllocated);
            
            // Update receipt status
            $receipt->update([
                'Status' => 'Posted',
                'ApprovalReason' => $validated['Reason'],
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now()
            ]);
            
            DB::commit();
            
            activity('Receipt Posting')
                ->performedOn($receipt)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'posted'])
                ->log("Posted receipt {$receipt->ReceiptNumber} to GL");

            return back()->with('success', "Receipt {$receipt->ReceiptNumber} posted successfully.");
        } catch (\Exception $e) {
            Log::error('Receipt posting failed', [
                'receipt_id' => $receipt->Id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Receipt posting failed: ' . $e->getMessage());
        }
    }

    /**
     * Get customer wallet balance
     */
    public function getWalletBalance(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:t_ThirdParties,Id'
        ]);

        $wallet = CustomerWallet::where('CustomerID', $request->customer_id)->active()->first();

        return response()->json([
            'balance' => $wallet ? $wallet->Balance : 0,
            'has_wallet' => (bool) $wallet
        ]);
    }

    /**
     * Restore credit when payment is received for an invoice with applied credit
     */
    private function restoreCreditFromPayment(FinanceInvoice $invoice, float $paymentAmount)
    {
        // Find active credit profile for this customer
        $creditProfile = \App\Models\Finance\FinanceCreditManagement::where('CustomerID', $invoice->CustomerID)
            ->whereRaw('LOWER(Status) = ?', ['approved'])
            ->where(function($query) {
                $query->where('ExpiryDate', '>=', now()->toDateString())
                      ->orWhereNull('ExpiryDate');
            })
            ->first();

        if (!$creditProfile) {
            Log::warning("No active credit profile found for customer {$invoice->CustomerID}");
            return;
        }

        // Create credit movement for payment received
        FinanceCreditMovement::create([
            'CreditID' => $creditProfile->Id,
            'CustomerID' => $invoice->CustomerID,
            'MovementType' => 'payment_received',
            'Amount' => $paymentAmount, // Positive because payment restores available credit
            'ReferenceType' => 'invoice_payment',
            'ReferenceID' => $invoice->Id,
            'Notes' => "Payment received KSh " . number_format($paymentAmount, 2) . " for invoice {$invoice->InvoiceNumber} - credit restored",
            'EffectiveOn' => now()->toDateString(),
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);
    }

    /**
     * Post receipt to GL using mappings from finance/glpostingmap (following credit management pattern)
     */
    private function postReceiptToGL(FinanceReceipt $receipt, string $reason, float $allocatedAmount = null)
    {
        try {
            $transactionService = app(TransactionService::class);
            
            $payload = [
                'ModuleID'          => 1100000, // Finance module
                'ThirdPartyID'      => $receipt->CustomerID,
                'TransactionTypeID' => 20, // Receipts transaction type from seeder
                'TransactionType'   => 'Receipt Posting',
                'ReferenceNumber'   => $receipt->ReceiptNumber,
                'TransactionDate'   => $receipt->PostingDate,
                'Amount'            => $allocatedAmount ?? (float)$receipt->AmountReceived,
                'TaxAmount'         => 0.00,
                'BranchID'          => session('LoginBranchId', 1),
                'DepartmentID'      => null,
                'CurrencyID'        => 56, // KES
                'CurrencyCode'      => 'KES',
                'ExchangeRate'      => 1.0,
                'Narration'         => "Receipt {$receipt->ReceiptNumber} from {$receipt->customer->ThirdPartyName}: {$reason}",
                'SourceTable'       => 't_FinanceReceipts',
                'SystemDescription' => "Receipt posting - {$receipt->ReceiptNumber}",
                'IdempotencyKey'    => "receipt_posting_{$receipt->Id}",
            ];

            $result = $transactionService->postFromTypeMapping($payload);

            if ($result['status'] === 'success') {
                Log::info("Receipt {$receipt->ReceiptNumber} posted to GL successfully", [
                    'batch_number' => $result['batch_number'] ?? null,
                    'amount' => $receipt->AmountReceived
                ]);
            } else {
                throw new \Exception('GL posting failed: ' . ($result['message'] ?? 'Unknown error'));
            }

        } catch (\Throwable $e) {
            Log::error('Receipt GL posting failed', [
                'receipt_id' => $receipt->Id,
                'receipt_number' => $receipt->ReceiptNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw new \Exception("Failed to post receipt to GL: " . $e->getMessage());
        }
    }

    /**
     * Get GL account for payment method
     */
    private function getPaymentMethodGLAccount(string $paymentMethod): int
    {
        // Get from GL mapping or use defaults
        return match(strtolower($paymentMethod)) {
            'cash' => 1001, // Cash account
            'bank', 'bank_transfer' => 1002, // Bank account
            'mpesa', 'm-pesa' => 1003, // Mobile money account
            'cheque' => 1002, // Bank account
            default => 1001 // Default to cash
        };
    }
}
