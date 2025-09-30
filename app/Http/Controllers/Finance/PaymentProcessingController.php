<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceTransaction;
use App\Models\Finance\FinanceVoucher;
use App\Services\Finance\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentProcessingController extends Controller
{
    public function index()
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceVoucher::class);

        $vouchers = FinanceVoucher::with('invoice:Id,InvoiceNumber')
            ->select('Id', 'VoucherNo', 'InvoiceNo', 'TotalAmount', 'PaymentMethod',
                'ApprovalStatus','PaymentType', 'Description','Status','IsProcessed')->where('ApprovalStatus', 'posted')
            ->get();
        return view('finance.accountspayable.paymentprocessing.index',compact('vouchers'));
    }

    public function create(){

        // $this->authorize('create', PaymentProcessing::class);
        $vouchers = FinanceVoucher::select('Id', 'VoucherNo', 'InvoiceNo', 'TotAmnt')
            ->where('Status','Approved')
            ->get();

        return view('finance.accountspayable.paymentprocessing.create', compact('vouchers'));
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::FinanceAccountsPayableView, FinanceVoucher::class);

        $voucher = FinanceVoucher::with('invoice.supplier')->findOrFail($id);
        $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $voucher->InvoiceNo)->where('ApprovalStatus','posted')->sum('TotalAmount');

        return view('finance.accountspayable.paymentprocessing.show', compact('voucher', 'amtPaidOnInvoice'));
    }

    public function postVoucher(Request $request, TransactionService $svc){
        $this->authorize(PermissionEnum::FinanceAccountsPayableCreate, FinanceVoucher::class);

        $validated = $request->validate([
            'Reason' => 'required|string|max:255',
            'InvoiceID' => 'required|integer|exists:t_FinanceInvoiceEntry,Id',
            'VoucherID' => 'required|integer|exists:t_FinanceVoucher,Id',
        ]);

        $invoiceID=$validated['InvoiceID'];
        $voucherID=$validated['VoucherID'];

        // Configure your module + transaction type mapping IDs
        // Make sure these exist in t_Modules and t_FinanceTransactionTypes
        $MODULE_ID          = 1100000; // Finance module
        $TRANSACTION_TYPEID = 17;    // "AP Voucher Processing"

        try {
            return DB::transaction(function () use ($invoiceID, $voucherID, $validated, $svc, $MODULE_ID, $TRANSACTION_TYPEID) {

                // Load the invoice with the same relations, and lock row for update
                $invoice = FinanceInvoiceEntry::with([
                    'thirdParty:Id,TradingName,ThirdPartyName',
                    'currency:Id,Name,Code,Symbol',
                    'order:Id,OrderNo,Description,OrdTotExcl',
                    'grn:id,GRNID,SupplierId',
                    'createdBy:Id,Name',
                ])
                    ->lockForUpdate()
                    ->findOrFail($invoiceID);

                $voucher = FinanceVoucher::findOrFail($voucherID);

                //Record as Scheduled or Not scheduled Voucher
                $isScheduled=false;
                if($voucher->PaymentType==='Scheduled'){
                    $isScheduled=true;
                }

                // Guard: already posted?
                if ($voucher->IsProcessed) {
                    return back()->with('error', "Voucher $voucher->VoucherNo is already Processed.");
                }

                // Build payload for TransactionService (service does idempotency)
                $payload = [
                    'ModuleID'          => $MODULE_ID,
                    'ThirdPartyID'=>$invoice->ThirdPartyID,
                    'IsScheduled'=>$isScheduled,
                    'VoucherID'=>$voucherID,
                    'TransactionTypeID' => $TRANSACTION_TYPEID,
                    'TransactionType'   => 'Voucher Processing',
                    'ReferenceNumber'   => $voucher->VoucherNo,
                    'TransactionDate'   => $invoice->InvoiceDate ?? now()->toDateString(),
                    'Amount'            => (float)($voucher->TotalAmount ?? 0),   // net (excl. tax) if that's your model
                    'TaxAmount'         => (float)($invoice->TaxAmount ?? 0),      // 0 if not captured
                    'BranchID'          => session('LoginBranchId', 1),
                    'DepartmentID'      => $invoice->DepartmentID ?? null,
                    'CurrencyID'        => $invoice->CurrencyID ?? 1,
                    'CurrencyCode'      => optional($invoice->currency)->Code ?? 'KES',
                    'ExchangeRate'      => (float)($invoice->ExchangeRate ?? 1),
                    'Narration'         => trim(($voucher->Description ?? '').' '.$validated['Reason']),
                    'SourceTable'       => 't_FinanceVoucher',
                    'SystemDescription' => 'Voucher Processing: '.$voucher->VoucherNo,
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

                // Update Voucher processin status
                if (in_array($result['status'], ['success', 'exists'], true)) {
                    $voucher->update([
                        'IsProcessed' => true,
                        'ModifiedBy'     => Auth::id(),
                        'ModifiedOn'     => now(),
                    ]);
                }

                // Prefer a user-friendly flash message
                $message = $result['status'] === 'exists'
                    ? "Voucher {$invoice->InvoiceNumber} was already Processed (idempotent)."
                    : ($result['message'] ?? "Voucher {$invoice->InvoiceNumber} Processed successfully.");

                $flashKey = $result['status'] === 'success' ? 'success' : 'info';

                activity('Transaction Posting')
                    ->performedOn(new FinanceVoucher())
                    ->causedBy(Auth::id())
                    ->withProperties(['Posting Transaction' => 'Posted from Account payable Voucher Processing.'])
                    ->log('Posted Transaction from Accounts Payable  Voucher Processing.');

                return back()->with($flashKey, $message);
            });
        } catch (\Throwable $e) {
            Log::error('Voucher Processing Error', ['err'=>$e->getMessage()]);
            return $e->getMessage();
            return back()->with('error', "Processing failed: ".$e->getMessage());
        }
    }

}
