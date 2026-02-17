<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceVoucher;
use App\Models\Procurement\RFQAward;
use App\Models\Procurement\TenderAward;
use App\Services\Finance\ContractInvoiceEligibilityService;
use App\Services\Finance\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentProcessingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::PaymentProcessingView, FinanceVoucher::class);

        $query = FinanceVoucher::with('invoice:Id,InvoiceNumber')
            ->select(
                'Id',
                'VoucherNo',
                'InvoiceNo',
                'TotalAmount',
                'PaymentMethod',
                'ApprovalStatus',
                'PaymentType',
                'Description',
                'Status',
                'IsProcessed'
            )
            ->where('ApprovalStatus', 'posted');

        if ($request->filled('voucher_no')) {
            $query->where('VoucherNo', 'like', '%' . $request->voucher_no . '%');
        }
        if ($request->filled('invoice_number')) {
            $invNum = $request->invoice_number;
            $query->whereHas('invoice', function ($q) use ($invNum) {
                $q->where('InvoiceNumber', 'like', '%' . $invNum . '%');
            });
        }
        if ($request->filled('payment_method')) {
            $query->where('PaymentMethod', $request->payment_method);
        }
        if ($request->filled('payment_type')) {
            $query->where('PaymentType', $request->payment_type);
        }
        if ($request->filled('processed')) {
            $query->where('IsProcessed', filter_var($request->processed, FILTER_VALIDATE_BOOLEAN));
        }
        if ($request->filled('amount_min')) {
            $query->where('TotalAmount', '>=', (float)$request->amount_min);
        }

        $sortField = $request->sort_by ?? 'Id';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int)($request->per_page ?? 10);
        $vouchers = $query->paginate($perPage)->withQueryString();

        return view('finance.accountspayable.paymentprocessing.index', compact('vouchers'));
    }

    public function create()
    {

        $this->authorize(PermissionEnum::PaymentProcessingCreate, FinanceVoucher::class);
        $vouchers = FinanceVoucher::select('Id', 'VoucherNo', 'InvoiceNo', 'TotAmnt')
            ->where('Status', 'Approved')
            ->get();

        return view('finance.accountspayable.paymentprocessing.create', compact('vouchers'));
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::PaymentProcessingView, FinanceVoucher::class);

        $voucher = FinanceVoucher::with([
            'invoice.supplier',
            'invoice.thirdParty',
            'invoice.currency',
            'invoice.contractPenaltyEvents',
        ])->findOrFail($id);
        if ($voucher->invoice && strtoupper((string) ($voucher->invoice->InvoiceSourceType ?? 'PO')) === 'CONTRACT') {
            app(ContractInvoiceEligibilityService::class)->refreshInvoiceHoldStatus($voucher->invoice);
            $voucher->load([
                'invoice.supplier',
                'invoice.thirdParty',
                'invoice.currency',
                'invoice.contractPenaltyEvents',
            ]);
        }
        $invoicePayload = $voucher->invoice
            ? $this->buildInvoicePreviewPayload($voucher->invoice)
            : null;
        $amtPaidOnInvoice = FinanceVoucher::where('InvoiceNo', $voucher->InvoiceNo)
            ->where('ApprovalStatus', 'posted')
            ->sum('TotalAmount');
        $invoiceReferenceAmount = (float) ($voucher->invoice->TotalAmount ?? $voucher->invoice->InvoiceAmount ?? 0);
        $invoiceBalance = round($invoiceReferenceAmount - (float) $amtPaidOnInvoice, 2);

        return view('finance.accountspayable.paymentprocessing.show', compact(
            'voucher',
            'amtPaidOnInvoice',
            'invoiceReferenceAmount',
            'invoiceBalance',
            'invoicePayload'
        ));
    }

    private function buildInvoicePreviewPayload(FinanceInvoiceEntry $invoice): array
    {
        $invoice->loadMissing([
            'currency:Id,Code,Symbol',
            'milestoneAllocations.milestone.checklistItems',
        ]);

        $sourceType = strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO'));
        $currencyCode = $invoice->currency->Code ?? 'KES';
        $currencySymbol = $invoice->currency->Symbol ?? $currencyCode;

        $invoiceBeforeTax = round((float) ($invoice->InvoiceAmount ?? 0), 2);
        $invoiceTaxAmount = round((float) ($invoice->TaxAmount ?? 0), 2);
        $invoiceTaxPct = round((float) ($invoice->TaxPercentage ?? 0), 4);
        $invoiceTotal = round((float) ($invoice->TotalAmount ?? ($invoiceBeforeTax + $invoiceTaxAmount)), 2);

        $amtPaidOnInvoice = (float) FinanceVoucher::where('InvoiceNo', $invoice->Id)
            ->where('ApprovalStatus', 'posted')
            ->sum('TotalAmount');
        $balance = round(max(0, $invoiceTotal - $amtPaidOnInvoice), 2);

        $attachments = $invoice->documents()
            ->get(['t_Documents.Id', 't_Documents.DocumentId', 'Name', 'MimeType'])
            ->map(function ($doc) {
                return [
                    'id' => (int) $doc->Id,
                    'document_id' => $doc->DocumentId,
                    'name' => $doc->Name,
                    'mime_type' => $doc->MimeType,
                ];
            })->values();

        $response = [
            'invoice' => [
                'id' => (int) $invoice->Id,
                'invoice_number' => $invoice->InvoiceNumber,
                'source_type' => $sourceType,
                'view_url' => route('invoiceentry.show', $invoice->Id),
                'invoice_date' => ! empty($invoice->InvoiceDate) ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('Y-m-d') : null,
                'due_date' => ! empty($invoice->DueDate) ? \Carbon\Carbon::parse($invoice->DueDate)->format('Y-m-d') : null,
                'before_tax' => $invoiceBeforeTax,
                'tax_amount' => $invoiceTaxAmount,
                'tax_percentage' => $invoiceTaxPct,
                'total_amount' => $invoiceTotal,
                'amount_paid' => round($amtPaidOnInvoice, 2),
                'balance' => $balance,
                'description' => $invoice->Description,
                'currency_code' => $currencyCode,
                'currency_symbol' => $currencySymbol,
            ],
            'attachments' => $attachments,
            'contract' => null,
            'po' => null,
        ];

        if ($sourceType === 'CONTRACT') {
            $reference = null;
            $contractType = strtolower((string) ($invoice->ContractSourceType ?? ''));
            $contractId = (int) ($invoice->ContractSourceID ?? 0);
            if ($contractType === 'tender') {
                $reference = TenderAward::where('Id', $contractId)->value('ContractRef');
            } elseif ($contractType === 'rfq') {
                $reference = RFQAward::where('Id', $contractId)->value('ContractRef');
            }

            $milestones = $invoice->milestoneAllocations
                ->sortBy(fn ($a) => (int) ($a->milestone->MilestoneNo ?? PHP_INT_MAX))
                ->values()
                ->map(function ($allocation) {
                    $milestone = $allocation->milestone;
                    $checklistItems = $milestone?->checklistItems ?? collect();

                    $requiredTotal = $checklistItems->where('Required', true)->count();
                    $requiredDone = $checklistItems->where('Required', true)->where('IsFulfilled', true)->count();

                    return [
                        'milestone_id' => (int) ($allocation->MilestoneID ?? 0),
                        'milestone_no' => (int) ($milestone->MilestoneNo ?? 0),
                        'title' => $milestone->Title ?? ('Milestone ' . (int) ($allocation->MilestoneID ?? 0)),
                        'status' => $milestone->Status,
                        'due_date' => ! empty($milestone?->PlannedDueDate) ? \Carbon\Carbon::parse($milestone->PlannedDueDate)->format('Y-m-d') : null,
                        'billed_amount' => round((float) ($allocation->BilledAmount ?? 0), 2),
                        'required_checklist_total' => (int) $requiredTotal,
                        'required_checklist_fulfilled' => (int) $requiredDone,
                        'checklist_items' => $checklistItems->map(function ($item) {
                            return [
                                'id' => (int) $item->Id,
                                'description' => $item->ItemDescription,
                                'required' => (bool) $item->Required,
                                'fulfilled' => (bool) $item->IsFulfilled,
                                'notes' => $item->Notes,
                            ];
                        })->values(),
                    ];
                });

            $response['contract'] = [
                'source_type' => $contractType,
                'source_id' => $contractId,
                'reference' => $reference ?: strtoupper($contractType) . '-CONTRACT-' . $contractId,
                'is_on_hold' => (bool) $invoice->IsOnHold,
                'hold_reason' => $invoice->HoldReason,
                'penalty_suggested_amount' => (float) ($invoice->PenaltySuggestedAmount ?? 0),
                'milestones' => $milestones,
            ];
        } else {
            $po = null;
            $grn = null;
            $grnItems = collect();
            $poId = (int) ($invoice->POId ?? $invoice->POReference ?? 0);
            $grnId = (int) ($invoice->GRNId ?? $invoice->GRNReference ?? 0);

            if ($poId > 0) {
                $po = DB::table('t_Orders')
                    ->where('Id', $poId)
                    ->select('Id', 'OrderNo', 'OrderDate', 'Description', 'OrdTotExcl', 'OrdDiscAmnt', 'TaxPercentage', 'OrdTotIncl')
                    ->first();
            }

            if ($grnId > 0) {
                $grn = DB::table('t_GoodsReceipts')
                    ->where('id', $grnId)
                    ->select('id', 'GRNID', 'POID', 'ReceivedDate')
                    ->first();

                if ($grn && ! empty($grn->GRNID)) {
                    $grnItems = DB::table('t_GoodsReceipts as gr')
                        ->leftJoin('t_Items as i', 'gr.iStockCodeID', '=', 'i.Id')
                        ->where('gr.GRNID', $grn->GRNID)
                        ->select(
                            DB::raw("COALESCE(i.ItemName, 'Item') as ItemName"),
                            DB::raw('COALESCE(gr.POQTY, 0) as POQTY'),
                            DB::raw('COALESCE(gr.ReceivedQTY, 0) as ReceivedQTY')
                        )
                        ->get()
                        ->map(function ($item) {
                            return [
                                'item_name' => $item->ItemName,
                                'po_qty' => (float) ($item->POQTY ?? 0),
                                'received_qty' => (float) ($item->ReceivedQTY ?? 0),
                            ];
                        })->values();
                }
            }

            $ordTotExcl = (float) ($po->OrdTotExcl ?? 0);
            $taxPct = (float) ($po->TaxPercentage ?? 0);
            $ordTotIncl = (float) ($po->OrdTotIncl ?? 0);
            if ($ordTotIncl <= 0 && $ordTotExcl > 0) {
                $ordTotIncl = round($ordTotExcl + ($ordTotExcl * ($taxPct / 100)), 2);
            }

            $response['po'] = [
                'order' => $po ? [
                    'id' => (int) $po->Id,
                    'order_no' => $po->OrderNo,
                    'order_date' => ! empty($po->OrderDate) ? \Carbon\Carbon::parse($po->OrderDate)->format('Y-m-d') : null,
                    'description' => $po->Description,
                    'before_tax' => $ordTotExcl,
                    'tax_percentage' => $taxPct,
                    'after_tax' => $ordTotIncl,
                ] : null,
                'grn' => $grn ? [
                    'id' => (int) $grn->id,
                    'grn_id' => $grn->GRNID,
                    'po_ref' => $grn->POID,
                    'received_date' => ! empty($grn->ReceivedDate) ? \Carbon\Carbon::parse($grn->ReceivedDate)->format('Y-m-d') : null,
                    'ordered_qty_total' => (float) $grnItems->sum('po_qty'),
                    'received_qty_total' => (float) $grnItems->sum('received_qty'),
                    'items' => $grnItems,
                ] : null,
            ];
        }

        return $response;
    }

    public function postVoucher(Request $request, TransactionService $svc)
    {

        $validated = $request->validate([
            'Reason' => 'required|string|max:255',
            'InvoiceID' => 'required|integer|exists:t_FinanceInvoiceEntry,Id',
            'VoucherID' => 'required|integer|exists:t_FinanceVoucher,Id',
        ]);

        $invoiceID = $validated['InvoiceID'];
        $voucherID = $validated['VoucherID'];

        // Configure your module + transaction type mapping IDs
        // Make sure these exist in t_Modules and t_FinanceTransactionTypes
        $MODULE_ID = 1100000; // Finance module
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
                $isScheduled = false;
                if ($voucher->PaymentType === 'Scheduled') {
                    $isScheduled = true;
                }

                $contractHoldPending = false;
                if (strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO')) === 'CONTRACT') {
                    app(ContractInvoiceEligibilityService::class)->refreshInvoiceHoldStatus($invoice);
                    $invoice->refresh();
                    $isPending = strtolower((string) ($invoice->MilestoneEligibilityStatus ?? 'pending')) === 'pending';
                    $contractHoldPending = (bool) $invoice->IsOnHold || $isPending;
                }

                if ($contractHoldPending) {
                    return back()->with('error', 'Milestone checklist is pending for this contract invoice. Please apply penalty or waive hold before processing.');
                }

                // Guard: already posted?
                if ($voucher->IsProcessed) {
                    return back()->with('error', "Voucher $voucher->VoucherNo is already Processed.");
                }

                // Build payload for TransactionService (service does idempotency)
                $payload = [
                    'ModuleID' => $MODULE_ID,
                    'ThirdPartyID' => $invoice->ThirdPartyID,
                    'IsScheduled' => $isScheduled,
                    'VoucherID' => $voucherID,
                    'TransactionTypeID' => $TRANSACTION_TYPEID,
                    'TransactionType' => 'Voucher Processing',
                    'ReferenceNumber' => $voucher->VoucherNo,
                    'TransactionDate' => $invoice->InvoiceDate ?? now()->toDateString(),
                    'Amount' => (float)($voucher->TotalAmount ?? 0),   // net (excl. tax) if that's your model
                    'TaxAmount' => (float)($invoice->TaxAmount ?? 0),      // 0 if not captured
                    'BranchID' => session('LoginBranchId', 1),
                    'DepartmentID' => $invoice->DepartmentID ?? null,
                    'CurrencyID' => $invoice->CurrencyID ?? 1,
                    'CurrencyCode' => optional($invoice->currency)->Code ?? 'KES',
                    'ExchangeRate' => (float)($invoice->ExchangeRate ?? 1),
                    'Narration' => trim(($voucher->Description ?? '') . ' ' . $validated['Reason']),
                    'SourceTable' => 't_FinanceVoucher',
                    'SystemDescription' => 'Voucher Processing: ' . $voucher->VoucherNo,
                    // Optional one‑off overrides if needed:
                    // 'DebitGLAccountID'  => 5_001,
                    // 'CreditGLAccountID' => 3_001,
                    // 'TaxGLAccountID'    => 2_101,
                ];
                // Post via mapping; TransactionService handles:
                $result = $svc->postFromTypeMapping($payload);

                // Update Voucher processin status
                if (in_array($result['status'], ['success', 'exists'], true)) {
                    $voucher->update([
                        'IsProcessed' => true,
                        'ModifiedBy' => Auth::id(),
                        'ModifiedOn' => now(),
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
            Log::error('Voucher Processing Error', ['err' => $e->getMessage()]);

            return $e->getMessage();

            return back()->with('error', "Processing failed: " . $e->getMessage());
        }
    }
}
