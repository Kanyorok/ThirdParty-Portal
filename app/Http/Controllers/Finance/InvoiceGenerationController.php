<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceCreditManagement;
use App\Models\Finance\FinanceCreditMovement;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceInvoiceLine;
use App\Models\Finance\FinanceTaxRuleConfiguration;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\InvoiceTax;
use App\Services\Finance\CreditCalculationService;
use App\Services\Finance\TransactionService;
use App\Services\ThirdParties\ThirdPartyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceGenerationController extends Controller
{
    protected $creditService;

    public function __construct(CreditCalculationService $creditService)
    {
        $this->creditService = $creditService;
    }

    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableView, FinanceInvoice::class);
        $query = FinanceInvoice::select([
            'Id',
            'RequestID',
            'InvoiceNumber',
            'InvoiceTitle',
            'CustomerID',
            'SourceTable',
            'CurrencyID',
            'ModuleID',
            'TotalAmount',
            'DueDate',
            'Status',
            'ApprovalStatus',
        ])
            ->with([
                'customer:Id,ThirdPartyName',
                'source:ModuleID,Name',
                'currency:Id,Code',
            ]);

        if ($request->filled('request_id')) {
            $query->where('RequestID', 'like', '%' . $request->request_id . '%');
        }
        if ($request->filled('invoice_number')) {
            $query->where('InvoiceNumber', 'like', '%' . $request->invoice_number . '%');
        }
        if ($request->filled('customer')) {
            $customer = $request->customer;
            $query->whereHas('customer', function ($q) use ($customer) {
                $q->where('ThirdPartyName', 'like', '%' . $customer . '%');
            });
        }
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('ApprovalStatus', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('DueDate', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('DueDate', '<=', $request->date_to);
        }

        $sortField = $request->sort_by ?? 'CreatedOn';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int)($request->per_page ?? 15);
        $invoices = $query->paginate($perPage)->withQueryString();

        return view('finance.accountsreceivable.invoicegeneration.index', compact('invoices'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableCreate, FinanceInvoice::class);
        // Get customers with their credit information
        $customers = DB::table('t_ThirdParties as tp')
            ->leftJoin('t_FinanceCreditManagement as fcm', function ($join) {
                $join->on('tp.Id', '=', 'fcm.CustomerID')
                    ->where('fcm.Status', '=', 'Approved')
                    ->whereRaw('fcm.EffectiveFrom <= GETDATE()')
                    ->whereRaw('fcm.ExpiryDate >= GETDATE()');
            })
            ->select([
                'tp.Id',
                'tp.ThirdPartyName',
                'tp.RegistrationNumber',
                'tp.Email',
                'fcm.CreditLimit',
                'fcm.EffectiveFrom as CreditEffectiveFrom',
            ])
            ->orderBy('tp.ThirdPartyName')
            ->get();

        return view('finance.accountsreceivable.invoicegeneration.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableCreate, FinanceInvoice::class);
        $validated = $request->validate([
            'CustomerID' => 'required|integer|exists:t_ThirdParties,Id',
            'InvoiceTitle' => 'required|string|max:255',
            'Description' => 'nullable|string|max:1000',
            'TotalAmount' => 'required|numeric|min:0.01',
            'TaxAmount' => 'nullable|numeric|min:0',
            'DueDate' => 'required|date|after_or_equal:today',
            'use_credit' => 'nullable|boolean',
            'lines' => 'required|array|min:1',
            'lines.*.description' => 'required|string|max:255',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_cost' => 'required|numeric|min:0',
            'lines.*.total' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Check credit availability if customer wants to use credit
            if ($validated['use_credit'] ?? false) {
                $creditCheck = $this->checkCreditAvailability($validated['CustomerID'], $validated['TotalAmount']);

                if (! $creditCheck['available']) {
                    return back()
                        ->withErrors(['credit' => $creditCheck['message']])
                        ->withInput();
                }
            }

            // Generate invoice number
            $invoiceNumber = $this->generateInvoiceNumber();

            // Create invoice
            $invoice = FinanceInvoice::create([
                'InvoiceNumber' => $invoiceNumber,
                'InvoiceTitle' => $validated['InvoiceTitle'],
                'CustomerID' => $validated['CustomerID'],
                'Description' => $validated['Description'],
                'InvoiceDate' => now()->toDateString(),
                'DueDate' => $validated['DueDate'],
                'TotalAmount' => $validated['TotalAmount'],
                'TaxAmount' => $validated['TaxAmount'] ?? 0,
                'Status' => 'draft',
                'ApprovalStatus' => 'pending',
                'UseCredit' => $validated['use_credit'] ?? false,
                'SourceTable' => 't_FinanceInvoice',
                'ModuleID' => 1100000, // Finance module
                'CurrencyID' => 56, // KES
                'ExchangeRate' => 1.0,
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Create invoice lines
            foreach ($validated['lines'] as $line) {
                FinanceInvoiceEntry::create([
                    'InvoiceID' => $invoice->Id,
                    'InvoiceLineName' => $line['description'],
                    'Description' => $line['description'],
                    'Quantity' => $line['quantity'],
                    'UnitCost' => $line['unit_cost'],
                    'Total' => $line['total'],
                    'Tax' => 0, // Calculate tax if needed
                    'TaxAmount' => 0,
                    'Discount' => 0,
                    'CreatedBy' => Auth::id(),
                    'CreatedOn' => now(),
                ]);
            }

            // If using credit, create a credit movement record
            if ($validated['use_credit'] ?? false) {
                $this->createCreditMovement($validated['CustomerID'], $validated['TotalAmount'], $invoice->Id);
            }

            activity('Invoice Creation')
                ->performedOn($invoice)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'created', 'use_credit' => $validated['use_credit'] ?? false])
                ->log("Created invoice {$invoiceNumber} for customer ID {$validated['CustomerID']}");

            DB::commit();

            return redirect()
                ->route('invoicegeneration.show', $invoice->Id)
                ->with('success', "Invoice {$invoiceNumber} created successfully" .
                    ($validated['use_credit'] ? ' using customer credit.' : '.'));
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Invoice creation failed', [
                'error' => $th->getMessage(),
                'customer_id' => $validated['CustomerID'] ?? null,
                'amount' => $validated['TotalAmount'] ?? null,
            ]);

            return back()
                ->withErrors(['error' => 'Failed to create invoice: ' . $th->getMessage()])
                ->withInput();
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsReceivableView, FinanceInvoice::class);
        $invoice = FinanceInvoice::with([
            'customer.country',
            'source:ModuleID,Name',
            'currency:Id,Code,Symbol,Name',
            'lines:Id,InvoiceID,InvoiceLineName,Description,UnitCost,Quantity,Tax,TaxAmount,Discount,Total',
        ])->findOrFail($id);

        // Get customer's credit information using the service
        $creditInfo = null;
        if ($invoice->CustomerID) {
            $creditSummary = $this->creditService->getCustomerCreditSummary($invoice->CustomerID);

            if ($creditSummary['has_credit']) {
                $creditInfo = [
                    'hasCredit' => true,
                    'creditLimit' => $creditSummary['credit_limit'],
                    'used' => $creditSummary['used'],
                    'available' => $creditSummary['available'],
                    'utilization' => $creditSummary['utilization_percentage'],
                    'canApplyCredit' => $creditSummary['available'] >= $invoice->TotalAmount,
                    'paymentTerms' => $creditSummary['payment_terms'],
                    'effectiveFrom' => $creditSummary['effective_from'],
                    'expiryDate' => $creditSummary['expiry_date'],
                ];
            } else {
                $creditInfo = [
                    'hasCredit' => false,
                    'message' => 'Customer has no active credit profile',
                ];
            }
        }

        return view('finance.accountsreceivable.invoicegeneration.show', compact('invoice', 'creditInfo'));
    }

    public function approve(Request $request, int $id, TransactionService $svc)
    {

        $validated = $request->validate([
            'Reason' => 'required|string|max:255',
        ]);

        // Configure your module + transaction type mapping IDs
        // Make sure these exist in t_Modules and t_FinanceTransactionTypes
        $MODULE_ID = 1100000;
        $TRANSACTION_TYPEID = 16;    // "AR Invoice"

        try {
            return DB::transaction(function () use ($id, $validated, $svc, $MODULE_ID, $TRANSACTION_TYPEID) {

                // Load the invoice with the same relations, and lock row for update
                $invoice = FinanceInvoice::with([
                    'customer:Id,ThirdPartyName',
                    'customer.types:TypeId,Code,Description',
                    'currency:Id,Name,Code,Symbol',
                    'createdBy:Id,Name',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                // Resolve ThirdPartyTypeID dynamically from the customer types.
                // Avoid hardcoding numeric IDs, since TypeId values vary per environment/seed data.
                $thirdPartyTypeId = null;
                if ($invoice->customer && $invoice->customer->relationLoaded('types')) {
                    $type =
                        $invoice->customer->types->firstWhere('Code', ThirdPartyService::TypeTenant)
                        ?? $invoice->customer->types->firstWhere('Code', ThirdPartyService::TypeCustomer)
                        ?? $invoice->customer->types->first();

                    $thirdPartyTypeId = $type?->TypeId;
                }

                // Guard: already posted?
                if (strtolower((string)$invoice->ApprovalStatus) === 'posted') {
                    return back()->with('error', "Invoice $invoice->InvoiceNumber is already posted.");
                }

                // Build payload for TransactionService (service does idempotency)
                $payload = [
                    'ModuleID' => $MODULE_ID,
                    'ThirdPartyID' => $invoice->CustomerID,
                    // Nullable is OK; TransactionService validates existence only if value is present.
                    'ThirdPartyTypeID' => $thirdPartyTypeId,
                    'TransactionTypeID' => $TRANSACTION_TYPEID,
                    'TransactionType' => 'Account Receivables Invoice',
                    'ReferenceNumber' => $invoice->InvoiceNumber,
                    'TransactionDate' => $invoice->InvoiceDate ?? now()->toDateString(),
                    'Amount' => (float)($invoice->TotalAmount ?? 0) - (float)($invoice->TaxAmount ?? 0),
                    'TaxAmount' => (float)($invoice->TaxAmount ?? 0),      // 0 if not captured
                    'BranchID' => session('LoginBranchId', 1),
                    'DepartmentID' => $invoice->DepartmentID ?? null,
                    'CurrencyID' => $invoice->CurrencyID ?? 56,
                    'CurrencyCode' => optional($invoice->currency)->Code ?? 'KES',
                    'ExchangeRate' => (float)($invoice->ExchangeRate ?? 1),
                    'Narration' => trim(($invoice->Description ?? '') . ' ' . $validated['Reason']),
                    'SourceTable' => $invoice->SourceTable,
                    'SystemDescription' => 'AR Invoice ' . $invoice->InvoiceNumber,
                    // Optional one‑off overrides if needed:
                    // 'DebitGLAccountID'  => 5_001,
                    // 'CreditGLAccountID' => 3_001,
                    // 'TaxGLAccountID'    => 2_101,
                ];
                // Post via mapping; TransactionService handles:
                $result = $svc->postFromTypeMapping($payload);

                // Update invoice approval status if posted (or keep as-is if service reported 'exists')
                if (in_array($result['status'], ['success', 'exists'], true)) {
                    $invoice->update([
                        'ApprovalStatus' => 'posted',
                        'ApprovalReason' => $validated['Reason'],
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);

                    //Pull all the taxes for that invoice distinctly from the t_FinanceInvoiceLines table
                    $invoiceTaxes = FinanceInvoiceLine::where('InvoiceID', $invoice->Id)
                        ->select('TaxID')
                        ->distinct()
                        ->get();
                    foreach ($invoiceTaxes as $tax) {
                        //Get more Tax info using the TaxID from the t_FinanceTaxRuleConfiguration table
                        $taxRule = FinanceTaxRuleConfiguration::find($tax->TaxID);
                        //Calculate the TaxAmount based on the TaxRate and the TotalAmount of the invoice
                        $taxAmount = $invoice->TotalAmount * ($taxRule->Rate / 100);
                        InvoiceTax::create([
                            'ARInvoiceID' => $invoice->Id,
                            'TaxID' => $tax->TaxID,
                            'TaxAmount' => $taxAmount,
                            'TaxPercentage' => $taxRule->Rate,
                            'AmountPaid' => 0,
                            'SourceType' => 'AR',
                            'Sourcetable' => 't_FinanceInvoices',
                            'CreatedBy' => Auth::id(),
                            'CreatedOn' => now(),
                            'ModifiedBy' => Auth::id(),
                            'ModifiedOn' => now(),
                        ]);
                    }
                }

                // Prefer a user-friendly flash message
                $message = $result['status'] === 'exists'
                    ? "Invoice {$invoice->InvoiceNumber} was already posted (idempotent)."
                    : ($result['message'] ?? "Invoice {$invoice->InvoiceNumber} posted successfully.");

                $flashKey = $result['status'] === 'success' ? 'success' : 'info';

                activity('Transaction Posting')
                    ->performedOn(new FinanceTransaction())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Posted from Account Receivables Invoice'])
                    ->log('Posted Transaction from Accounts Receivables Invoice');

                return back()->with($flashKey, $message);
            });
        } catch (\Throwable $e) {
            // Log if you want: Log::error('AP approve error', ['id'=>$id, 'err'=>$e->getMessage()])
            return $e->getMessage();

            return back()->with('error', "Approval/Post failed: " . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'Reason' => 'required|string|max:1000',
        ]);

        try {
            return DB::transaction(function () use ($validated, $id) {
                // Lock the row for update to avoid race conditions
                $invoice = FinanceInvoiceEntry::with([
                    'supplier:Id,SupplierName',
                    'currency:Id,Name,Code,Symbol',
                    'order:Id,OrderNo,Description,OrdTotExcl',
                    'grn:id,GRNID,SupplierId',
                    'createdBy:Id,Name',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                // If already processed, prevent duplicate rejection
                if (in_array($invoice->ApprovalStatus, ['posted', 'rejected'], true)) {
                    $apStatus = ucfirst($invoice->ApprovalStatus);

                    return back()->with('error', "Invoice {$invoice->InvoiceNumber} is already {$apStatus}.");
                }

                // Update status & reason
                $invoice->update([
                    'ApprovalStatus' => 'rejected',
                    'ApprovalReason' => $validated['Reason'],
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                activity('Transaction Posting')
                    ->performedOn(new FinanceInvoiceEntry())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Rejected from Account payable Invoice'])
                    ->log('Rejected Transaction from Accounts Payable Invoice');

                return back()->with('success', "Invoice {$invoice->InvoiceNumber} rejected successfully.");
            });
        } catch (\Throwable $e) {
            Log::error('AP reject error', ['id' => $id, 'err' => $e->getMessage()]);

            return back()->with('error', "Approval/Post failed: " . $e->getMessage());
        }
    }

    /**
     * Check if customer has sufficient credit for the invoice amount
     */
    private function checkCreditAvailability(int $customerId, float $invoiceAmount): array
    {

        return $this->creditService->canApplyCredit($customerId, $invoiceAmount);
    }

    /**
     * Resolve customer ID for credit operations
     * Handles cases where property invoices have different customer ID mapping
     */
    private function resolveCustomerIdForCredit(FinanceInvoice $invoice): ?int
    {
        // First try direct CustomerID match
        $credit = FinanceCreditManagement::where('CustomerID', $invoice->CustomerID)
            ->whereRaw('LOWER(Status) = ?', ['approved'])
            ->first();

        if ($credit) {
            return $invoice->CustomerID;
        }

        // If no direct match and invoice is from property, try by customer name
        if ($invoice->SourceTable === 't_RentInvoice' && $invoice->customer) {
            $customerName = $invoice->customer->ThirdPartyName;

            $creditByName = FinanceCreditManagement::whereHas('customer', function ($query) use ($customerName) {
                $query->where('ThirdPartyName', 'like', "%{$customerName}%");
            })
                ->whereRaw('LOWER(Status) = ?', ['approved'])
                ->first();

            if ($creditByName) {
                return $creditByName->CustomerID;
            }
        }

        return null;
    }

    /**
     * Create credit movement when invoice uses credit
     * NOTE: This only tracks credit utilization, no GL posting
     * GL posting is handled by invoice approval process
     */
    private function createCreditMovement(int $customerId, float $amount, int $invoiceId): void
    {
        $credit = FinanceCreditManagement::where('CustomerID', $customerId)
            ->whereRaw('LOWER(Status) = ?', ['approved'])
            ->first();

        if ($credit) {
            $movement = FinanceCreditMovement::create([
                'CreditID' => $credit->Id,
                'CustomerID' => $customerId,
                'MovementType' => 'invoice_usage',
                'Amount' => -$amount, // Negative because it decreases available credit
                'ReferenceType' => 'invoice',
                'ReferenceID' => $invoiceId,
                'Notes' => "Credit applied to invoice #{$invoiceId} (tracking only, GL posted via invoice approval)",
                'EffectiveOn' => now(),
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Log credit movement creation for debugging
            Log::info('Credit movement created', [
                'movement_id' => $movement->Id,
                'credit_id' => $credit->Id,
                'customer_id' => $customerId,
                'invoice_id' => $invoiceId,
                'amount' => $amount,
            ]);
        } else {
            Log::warning('No approved credit found for customer', [
                'customer_id' => $customerId,
                'invoice_id' => $invoiceId,
            ]);
        }
    }

    /**
     * Generate unique invoice number
     */
    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');

        // Get the last invoice number for this month
        $lastInvoice = FinanceInvoice::where('InvoiceNumber', 'like', "{$prefix}-{$year}{$month}%")
            ->orderBy('InvoiceNumber', 'desc')
            ->first();

        if ($lastInvoice) {
            // Extract the sequence number and increment
            $lastNumber = (int)substr($lastInvoice->InvoiceNumber, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return sprintf('%s-%s%s%04d', $prefix, $year, $month, $newNumber);
    }

    /**
     * AJAX endpoint to check customer credit availability
     */
    public function checkCredit(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer|exists:t_ThirdParties,Id',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $creditCheck = $this->checkCreditAvailability($request->customer_id, $request->amount);

        return response()->json([
            'available' => $creditCheck['available'],
            'message' => $creditCheck['message'],
            'available_amount' => $creditCheck['available_amount'] ?? 0,
            'credit_limit' => $creditCheck['credit_limit'] ?? 0,
        ]);
    }

    /**
     * Apply credit to an existing invoice
     */
    public function applyCredit(Request $request, $id)
    {
        $request->validate([
            'apply_credit' => 'required|boolean',
            'reason' => 'required|string|max:500',
            'proceed_to_approval' => 'nullable|boolean',
        ]);

        $invoice = FinanceInvoice::findOrFail($id);

        // Check if invoice is eligible for credit application
        if ($invoice->ApprovalStatus !== 'draft') {
            return back()->with('error', 'Credit can only be applied to draft invoices.');
        }

        DB::beginTransaction();

        try {
            if ($request->apply_credit) {
                // Resolve the correct customer ID for credit operations
                $creditCustomerId = $this->resolveCustomerIdForCredit($invoice);

                if (! $creditCustomerId) {
                    return back()->with('error', 'No active credit profile found for this customer.');
                }

                // Check credit availability using resolved customer ID
                $creditCheck = $this->checkCreditAvailability($creditCustomerId, $invoice->TotalAmount);

                if (! $creditCheck['available']) {
                    return back()->with('error', $creditCheck['message']);
                }

                // Apply credit to invoice
                $invoice->update([
                    'UseCredit' => true,
                    'CreditAppliedOn' => now(),
                    'CreditAppliedBy' => Auth::id(),
                    'CreditApplicationReason' => $request->reason,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Create credit movement using resolved customer ID (tracking only, no GL posting needed)
                $this->createCreditMovement($creditCustomerId, $invoice->TotalAmount, $invoice->Id);

                activity('Invoice Credit Application')
                    ->performedOn($invoice)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'credit_applied', 'amount' => $invoice->TotalAmount])
                    ->log("Applied credit to invoice {$invoice->InvoiceNumber}");

                // If user chose to proceed to approval, approve the invoice immediately
                if ($request->proceed_to_approval) {
                    $this->approveInvoiceWithCredit($invoice, $request->reason);
                    DB::commit();

                    return back()->with('success', "Credit applied and invoice {$invoice->InvoiceNumber} has been posted successfully.");
                }

                DB::commit();

                return back()->with('success', "Credit successfully applied to invoice {$invoice->InvoiceNumber}");
            } else {
                // Remove credit from invoice
                $invoice->update([
                    'UseCredit' => false,
                    'CreditAppliedOn' => null,
                    'CreditAppliedBy' => null,
                    'CreditApplicationReason' => null,
                    'ModifiedBy' => Auth::id(),
                    'ModifiedOn' => now(),
                ]);

                // Remove credit movement if exists
                FinanceCreditMovement::where('ReferenceType', 'invoice')
                    ->where('ReferenceID', $invoice->Id)
                    ->delete();

                activity('Invoice Credit Removal')
                    ->performedOn($invoice)
                    ->causedBy(Auth::user())
                    ->withProperties(['action' => 'credit_removed', 'amount' => $invoice->TotalAmount])
                    ->log("Removed credit from invoice {$invoice->InvoiceNumber}");

                DB::commit();

                return back()->with('success', "Credit removed from invoice {$invoice->InvoiceNumber}");
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Credit application failed', [
                'invoice_id' => $id,
                'customer_id' => $invoice->CustomerID,
                'error' => $th->getMessage(),
            ]);

            return back()->with('error', 'Failed to apply credit: ' . $th->getMessage());
        }
    }

    /**
     * Approve invoice with credit applied (internal method)
     */
    private function approveInvoiceWithCredit(FinanceInvoice $invoice, string $reason): void
    {
        // Use the existing approve logic from the approve method
        $MODULE_ID = 1100000;
        $TRANSACTION_TYPEID = 16; // "AR Invoice"

        // Load the invoice with relations
        $invoice->load([
            'customer:Id,ThirdPartyName',
            'currency:Id,Name,Code,Symbol',
            'createdBy:Id,Name',
        ]);

        // Build payload for TransactionService
        $payload = [
            'ModuleID' => $MODULE_ID,
            'ThirdPartyID' => $invoice->CustomerID,
            'TransactionTypeID' => $TRANSACTION_TYPEID,
            'TransactionType' => 'Account Receivables Invoice',
            'ReferenceNumber' => $invoice->InvoiceNumber,
            'TransactionDate' => $invoice->InvoiceDate ?? now()->toDateString(),
            'Amount' => (float)($invoice->TotalAmount ?? 0),
            'TaxAmount' => (float)($invoice->TaxAmount ?? 0),
            'BranchID' => session('LoginBranchId', 1),
            'DepartmentID' => $invoice->DepartmentID ?? null,
            'CurrencyID' => $invoice->CurrencyID ?? 56,
            'CurrencyCode' => optional($invoice->currency)->Code ?? 'KES',
            'ExchangeRate' => (float)($invoice->ExchangeRate ?? 1),
            'Narration' => trim(($invoice->Description ?? '') . ' ' . $reason . ' (Credit Applied)'),
            'SourceTable' => $invoice->SourceTable,
            'SystemDescription' => 'AR Invoice ' . $invoice->InvoiceNumber . ' (Credit Applied)',
        ];

        // Post via TransactionService
        $svc = app(TransactionService::class);
        $result = $svc->postFromTypeMapping($payload);

        // Update invoice approval status if posted
        if (in_array($result['status'], ['success', 'exists'], true)) {
            $invoice->update([
                'ApprovalStatus' => 'posted',
                'ApprovalReason' => $reason . ' (Credit Applied)',
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Update the credit movement
            if ($invoice->UseCredit) {
                FinanceCreditMovement::where('ReferenceType', 'invoice')
                    ->where('ReferenceID', $invoice->Id)
                    ->update([
                        'Notes' => "Credit applied to invoice #{$invoice->InvoiceNumber} - Invoice posted to GL",
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
                    ]);
            }

            activity('Invoice Approval with Credit')
                ->performedOn($invoice)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'approved_with_credit', 'amount' => $invoice->TotalAmount])
                ->log("Approved invoice {$invoice->InvoiceNumber} with credit applied");
        } else {
            throw new \Exception('Failed to post invoice to GL: ' . ($result['message'] ?? 'Unknown error'));
        }
    }
}
