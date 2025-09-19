<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoice;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceTransaction;
use App\Services\Finance\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceGenerationController extends Controller
{

    public function index()
    {
        $invoices = FinanceInvoice::select([
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
                'currency:Id,Code'
            ])
            ->orderByDesc('CreatedOn')
            ->paginate(15);

        return view('finance.accountsreceivable.invoicegeneration.index', compact('invoices'));
    }

    public function create(){
        return view('finance.accountsreceivable.invoicegeneration.create');
    }

    public function show($id)
    {
        $invoice = FinanceInvoice::with([
            'customer.country',
            'source:ModuleID,Name',
            'currency:Id,Code,Symbol,Name',
            'lines:Id,InvoiceID,InvoiceLineName,Description,UnitCost,Quantity,Tax,TaxAmount,Discount,Total'
        ])->findOrFail($id);

        return view('finance.accountsreceivable.invoicegeneration.show', compact('invoice'));
    }


    public function approve(Request $request, int $id, TransactionService $svc)
    {
        // $this->authorize('approve-ap-invoice', FinanceInvoiceEntry::class);

        $validated = $request->validate([
            'Reason' => 'required|string|max:255',
        ]);

        // Configure your module + transaction type mapping IDs
        // Make sure these exist in t_Modules and t_FinanceTransactionTypes
        $MODULE_ID          = 1100000;
        $TRANSACTION_TYPEID = 16;    // "AR Invoice"

        try {
            return DB::transaction(function () use ($id, $validated, $svc, $MODULE_ID, $TRANSACTION_TYPEID) {

                // Load the invoice with the same relations, and lock row for update
                $invoice = FinanceInvoice::with([
                    'customer:Id,ThirdPartyName',
                    'currency:Id,Name,Code,Symbol',
                    'createdBy:Id,Name',
                ])
                    ->lockForUpdate()
                    ->findOrFail($id);

                // Guard: already posted?
                if (strtolower((string)$invoice->ApprovalStatus) === 'posted') {
                    return back()->with('error', "Invoice $invoice->InvoiceNumber is already posted.");
                }

                // Build payload for TransactionService (service does idempotency)
                $payload = [
                    'ModuleID'          => $MODULE_ID,
                    'ThirdPartyID'      => $invoice->CustomerID,
                    'TransactionTypeID' => $TRANSACTION_TYPEID,
                    'TransactionType'   => 'Account Receivables Invoice',
                    'ReferenceNumber'   => $invoice->InvoiceNumber,
                    'TransactionDate'   => $invoice->InvoiceDate ?? now()->toDateString(),
                    'Amount'            => (float)($invoice->TotalAmount ?? 0),   // net (excl. tax) if that's your model
                    'TaxAmount'         => (float)($invoice->TaxAmount ?? 0),      // 0 if not captured
                    'BranchID'          => session('LoginBranchId', 1),
                    'DepartmentID'      => $invoice->DepartmentID ?? null,
                    'CurrencyID'        => $invoice->CurrencyID ?? 56,
                    'CurrencyCode'      => optional($invoice->currency)->Code ?? 'KES',
                    'ExchangeRate'      => (float)($invoice->ExchangeRate ?? 1),
                    'Narration'         => trim(($invoice->Description ?? '').' '.$validated['Reason']),
                    'SourceTable'       => $invoice->SourceTable,
                    'SystemDescription' => 'AR Invoice '.$invoice->InvoiceNumber,
                    // Optional one‑off overrides if needed:
                    // 'DebitGLAccountID'  => 5_001,
                    // 'CreditGLAccountID' => 3_001,
                    // 'TaxGLAccountID'    => 2_101,
                ];
                // Post via mapping; TransactionService handles:
                // - mapping lookup
                // - idempotency (no duplicates)
                // - validation + balancing
                // - persistence (single DB txn internally)
                $result = $svc->postFromTypeMapping($payload);

                // Update invoice approval status if posted (or keep as-is if service reported 'exists')
                if (in_array($result['status'], ['success', 'exists'], true)) {
                    $invoice->update([
                        'ApprovalStatus' => 'posted',
                        'ApprovalReason' => $validated['Reason'],
                        'ModifiedBy'     => Auth::id(),
                        'ModifiedOn'     => now(),
                    ]);
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
            return back()->with('error', "Approval/Post failed: ".$e->getMessage());
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
                    $apStatus=ucfirst($invoice->ApprovalStatus);
                    return back()->with('error', "Invoice {$invoice->InvoiceNumber} is already {$apStatus}.");
                }

                // Update status & reason
                $invoice->update([
                    'ApprovalStatus' => 'rejected',
                    'ApprovalReason' => $validated['Reason'],
                    'ModifiedBy'     => Auth::id(),
                    'ModifiedOn'     => now(),
                ]);

                activity('Transaction Posting')
                    ->performedOn(new FinanceInvoiceEntry())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Rejected from Account payable Invoice'])
                    ->log('Rejected Transaction from Accounts Payable Invoice');

                return back()->with('success', "Invoice {$invoice->InvoiceNumber} rejected successfully.");
            });
        }catch (\Throwable $e) {
            Log::error('AP reject error', ['id'=>$id, 'err'=>$e->getMessage()]);
            return back()->with('error', "Approval/Post failed: ".$e->getMessage());
        }
    }
}
