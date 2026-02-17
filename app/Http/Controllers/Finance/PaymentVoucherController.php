<?php

namespace App\Http\Controllers\Finance;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Finance\FinanceInvoiceEntry;
use App\Models\Finance\FinanceVoucher;
use App\Models\Procurement\ContractPenaltyEvent;
use App\Models\Procurement\RFQAward;
use App\Models\Procurement\TenderAward;
use App\Services\Finance\ContractInvoiceEligibilityService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentVoucherController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::PaymentVoucherView, FinanceVoucher::class);

        $query = FinanceVoucher::with('invoice:Id,InvoiceNumber')
            ->select(
                'Id',
                'VoucherNo',
                'InvoiceNo',
                'TotalAmount',
                'PaymentMethod',
                'ApprovalStatus',
                'PaymentType',
                'Description'
            );

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
        if ($request->filled('approval_status') && $request->approval_status !== 'all') {
            $query->where('ApprovalStatus', $request->approval_status);
        }
        if ($request->filled('payment_type')) {
            $query->where('PaymentType', $request->payment_type);
        }
        if ($request->filled('amount_min')) {
            $query->where('TotalAmount', '>=', (float)$request->amount_min);
        }

        $sortField = $request->sort_by ?? 'Id';
        $sortDirection = $request->sort_direction ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        $perPage = (int)($request->per_page ?? 10);
        $vouchers = $query->paginate($perPage)->withQueryString();

        return view('finance.accountspayable.paymentvoucher.index', compact('vouchers'));
    }

    public function create()
    {

        $this->authorize(PermissionEnum::PaymentVoucherCreate, FinanceVoucher::class);

        $year = now()->year;
        $lastId = FinanceVoucher::max('Id') + 1;
        $VoucherNo = 'VCN-' . $year .'-'. str_pad($lastId, 6,'0', STR_PAD_LEFT);
        $invoices=[];
        $contractExceptions = [];
        $data=FinanceInvoiceEntry::with('currency:Id,Code')
                ->select(
                    'Id',
                    'InvoiceNumber',
                    'SupplierID',
                    'CurrencyID',
                    'InvoiceAmount',
                    'TotalAmount',
                    'InvoiceSourceType',
                    'ContractSourceType',
                    'ContractSourceID',
                    'MilestoneEligibilityStatus',
                    'IsOnHold',
                    'HoldReason',
                    'PenaltySuggestedAmount'
                )
                ->where('ApprovalStatus', 'posted')
                ->get();
        foreach ($data as $value) {
            $isContract = strtoupper((string) ($value->InvoiceSourceType ?? 'PO')) === 'CONTRACT';
            if ($isContract) {
                app(ContractInvoiceEligibilityService::class)->refreshInvoiceHoldStatus($value);
                $value->refresh();
            }

            //Get the invoice balance
            $invoiceAmt=(float) ($value->TotalAmount ?? $value->InvoiceAmount ?? 0);
            $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $value->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
            $balance=round($invoiceAmt-(float)$amtPaidOnInvoice, 2);
            //Dont store invoies that are fully paid already
            if ($balance <= 0) {
                continue;
            }

            $hasMilestoneHold = $isContract && ((bool) $value->IsOnHold || strtolower((string) ($value->MilestoneEligibilityStatus ?? 'pending')) === 'pending');
            if ($hasMilestoneHold) {
                $contractExceptions[] = [
                    'Id' => $value->Id,
                    'InvoiceNumber' => $value->InvoiceNumber,
                    'HoldReason' => $value->HoldReason ?: 'Milestone acceptance pending.',
                    'PenaltySuggestedAmount' => (float) ($value->PenaltySuggestedAmount ?? 0),
                    'ContractSourceType' => $value->ContractSourceType,
                    'ContractSourceID' => $value->ContractSourceID,
                    'Balance' => $balance,
                    'CurrencyCode' => $value->currency->Code ?? 'KES',
                ];
            }
            $invoices[]=[
                'Id'=>$value->Id,
                'InvoiceNumber'=>$value->InvoiceNumber,
                // ThirdPartyID is stored in SupplierID field for AP module
                'ThirdPartyID' => $value->SupplierID,
                'CurrencyID'=>$value->CurrencyID,
                'InvoiceAmount'=>$invoiceAmt,
                'CurrencyCode'=>$value->currency->Code ?? 'KES',
                'Balance'=>$balance,
                'InvoiceSourceType' => $value->InvoiceSourceType ?? 'PO',
                'IsOnHold' => (bool) ($value->IsOnHold ?? false),
                'MilestoneEligibilityStatus' => (string) ($value->MilestoneEligibilityStatus ?? ''),
                'HoldReason' => (string) ($value->HoldReason ?? ''),
            ];
        }
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')->get();
        $paymentTypes = CodeDetail::where('CodeID', 'PaymentType')->get();
        $paymentFrequencies = CodeDetail::where('CodeID', 'PaymentFrequency')->get();

        return view('finance.accountspayable.paymentvoucher.create', compact(
            'invoices',
            'VoucherNo',
            'paymentMethods',
            'paymentTypes',
            'paymentFrequencies',
            'contractExceptions',
        ));
    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::PaymentVoucherCreate, FinanceVoucher::class);

        $validated = $request->validate([
            //'VoucherNo'=> 'required|string',
            'InvoiceNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
            'TotAmnt' => 'required|numeric|min:0.00',
            'PaymentMethod' => 'required|string',
            'PaymentType' => 'required|string',
            'StartDate' => 'nullable|date',
            'Frequency' => 'nullable|string',
            'Description' => 'required|string',
        ]);

        //Check if the Voucher amt exceeds the Invoice Balance and return back with an error
        $invoice=FinanceInvoiceEntry::find($validated['InvoiceNo']);
        if (strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO')) === 'CONTRACT') {
            app(ContractInvoiceEligibilityService::class)->refreshInvoiceHoldStatus($invoice);
            $invoice->refresh();
        }
        $invoiceAmt=(float) ($invoice->TotalAmount ?? $invoice->InvoiceAmount ?? 0);
        $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
        $balance=round($invoiceAmt-(float)$amtPaidOnInvoice, 2);
        if($balance==0){
            return back()->with('error', 'This Invoice is already settled. Current balance is '.$balance);
        }
        if ($balance < $validated['TotAmnt']) {
            return back()->with('error', 'Invoice Balance is Exceeded. The current balance is ' . $balance);
        }


        DB::beginTransaction();

        $startdate = $request->input('StartDate');
        $frequency = $request->input('Frequency');

        try {
            if ($validated['PaymentType'] == 'Full') {
                $startdate = null;
                $frequency = null;
            } elseif ($validated['PaymentType'] == 'Partial') {
                $startdate = null;
                $frequency = null;
            } else {
                $startdate = $request->input('StartDate');
                $frequency = $request->input('Frequency');
            }

            $voucher = FinanceVoucher::create([
//                'VoucherNo'=> $validated['VoucherNo'],
                'InvoiceNo' => $validated['InvoiceNo'],
                'TotalAmount' => $validated['TotAmnt'],
                'PaymentMethod' => $validated['PaymentMethod'],
                'PaymentType' => $validated['PaymentType'],
                'StartDate' => $startdate,
                'Frequency' => $frequency,
                'Description' => $validated['Description'],
                'CreatedBy' => Auth::Id(),
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Created Voucher Successfully' . $voucher->id);

            DB::commit();

            return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Created Successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());

            return back()->with('error', 'Ooops! An error occurred, Please try again later.');
        }
    }

    public function edit($id)
    {
        $this->authorize(PermissionEnum::PaymentVoucherUpdate, FinanceVoucher::class);

        $voucher = FinanceVoucher::findOrFail($id);

        $year = now()->year;
        $lastId = FinanceVoucher::max('Id') + 1;
        $VoucherNo = 'VCN-' . $year .'-'. str_pad($lastId, 6,'0', STR_PAD_LEFT);
        $invoices=[];
        $data=FinanceInvoiceEntry::with('currency:Id,Code')
                ->select('Id', 'InvoiceNumber','SupplierID','CurrencyID', 'InvoiceAmount', 'TotalAmount')
                ->where('ApprovalStatus', 'posted')
                ->get();
        foreach ($data as $value) {
            //Get the invoice balance
            $invoiceAmt=(float) ($value->TotalAmount ?? $value->InvoiceAmount ?? 0);
            $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $value->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
            $balance=round($invoiceAmt-(float)$amtPaidOnInvoice, 2);
            //Dont store invoies that are fully paid already
            if ($balance <= 0) {
                continue;
            }
            $invoices[] = [
                'Id' => $value->Id,
                'InvoiceNumber' => $value->InvoiceNumber,
                'ThirdPartyID' => $value->SupplierID,
                'CurrencyID'=>$value->CurrencyID,
                'InvoiceAmount'=>$invoiceAmt,
                'CurrencyCode'=>$value->currency->Code ?? 'KES',
                'Balance'=>$balance
            ];
        }
        $paymentMethods = CodeDetail::where('CodeID', 'PaymentMethod')->get();
        $paymentTypes = CodeDetail::where('CodeID', 'PaymentType')->get();
        $paymentFrequencies = CodeDetail::where('CodeID', 'PaymentFrequency')->get();


        return view('finance.accountspayable.paymentvoucher.edit', compact(
            'voucher',
            'invoices',
            'VoucherNo',
            'paymentMethods',
            'paymentTypes',
            'paymentFrequencies'
        ));
    }

    public function update(Request $request, $id)
    {
        $this->authorize(PermissionEnum::PaymentVoucherUpdate, FinanceVoucher::class);

        $validated = $request->validate([
            'VoucherNo' => 'required|string',
            'InvoiceNo' => 'required|exists:t_FinanceInvoiceEntry,Id',
            'TotAmnt' => 'required|numeric|min:0.00',
            'PaymentMethod' => 'required|string',
            'PaymentType' => 'required|string',
            'StartDate' => 'nullable|date',
            'Frequency' => 'nullable|string',
            'Description' => 'required|string',
        ]);

        $invoice=FinanceInvoiceEntry::find($validated['InvoiceNo']);
        if (strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO')) === 'CONTRACT') {
            app(ContractInvoiceEligibilityService::class)->refreshInvoiceHoldStatus($invoice);
            $invoice->refresh();
        }
        $invoiceAmt=(float) ($invoice->TotalAmount ?? $invoice->InvoiceAmount ?? 0);
        $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $invoice->Id)->where('ApprovalStatus','posted')->sum('TotalAmount');
        $balance=round($invoiceAmt-(float)$amtPaidOnInvoice, 2);
        if($balance==0){
            return back()->with('error', 'This Invoice is already settled. Current balance is '.$balance);
        }
        if ($balance < $validated['TotAmnt']) {
            return back()->with('error', 'Invoice Balance is Exceeded. The current balance is ' . $balance);
        }

        DB::beginTransaction();

        $startdate = $request->input('StartDate');
        $frequency = $request->input('Frequency');

        try {
            if ($validated['PaymentType'] == 'Full') {
                $startdate = null;
                $frequency = null;
            } elseif ($validated['PaymentType'] == 'Partial') {
                $startdate = null;
                $frequency = null;
            } else {
                $startdate = $request->input('StartDate');
                $frequency = $request->input('Frequency');
            }

            $voucher = FinanceVoucher::findOrFail($id);
            $voucher->update([
                'VoucherNo' => $validated['VoucherNo'],
                'InvoiceNo' => $validated['InvoiceNo'],
                'TotalAmount' => $validated['TotAmnt'],
                'PaymentMethod' => $validated['PaymentMethod'],
                'PaymentType' => $validated['PaymentType'],
                'StartDate' => $request->input('StartDate'),
                'Frequency' => $request->input('Frequency'),
                'Description' => $validated['Description'],
                'ModifiedBy' => Auth::Id(),
            ]);

            activity()
                ->performedOn($voucher)
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'update'])
                ->log('Updated Voucher Successfully' . $voucher->id);

            DB::commit();

            return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Updated Successfully');
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error($th->getMessage());

            return back()->with('error', $th->getMessage());
        }
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::PaymentVoucherView, FinanceVoucher::class);

        $voucher = FinanceVoucher::with([
            'invoice.thirdParty:Id,TradingName,ThirdPartyName,Email,Phone,PhysicalAddress',
            'invoice.currency:Id,Code',
        ])->findOrFail($id);

        $invoicePayload = $voucher->invoice
            ? $this->buildInvoicePreviewPayload($voucher->invoice)
            : null;

        $amtPaidOnInvoice=FinanceVoucher::where('InvoiceNo', $voucher->InvoiceNo)->where('ApprovalStatus','posted')->sum('TotalAmount');
        $invoiceReferenceAmount = (float) ($voucher->invoice->TotalAmount ?? $voucher->invoice->InvoiceAmount ?? 0);
        $invoiceBalance = round($invoiceReferenceAmount - (float) $amtPaidOnInvoice, 2);
        $statusClass = match($voucher->ApprovalStatus) {
            'posted' => 'bg-success',
            'rejected' => 'bg-danger',
            'draft' => 'bg-warning text-dark',
            default => 'bg-secondary'
        };

        // Convert Amount into words using inbuilt php function

        $amountInWords = $this->numberToWords($voucher->TotalAmount);
        return view('finance.accountspayable.paymentvoucher.show', compact('voucher', 'amtPaidOnInvoice','statusClass', 'amountInWords', 'invoiceReferenceAmount', 'invoiceBalance', 'invoicePayload'));
    }

    public function invoicePreview(int $invoiceId)
    {
        $this->authorize(PermissionEnum::PaymentVoucherCreate, FinanceVoucher::class);

        $invoice = FinanceInvoiceEntry::with([
            'currency:Id,Code,Symbol',
            'milestoneAllocations.milestone.checklistItems',
        ])->findOrFail($invoiceId);

        return response()->json($this->buildInvoicePreviewPayload($invoice));
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
                'invoice_date' => !empty($invoice->InvoiceDate) ? \Carbon\Carbon::parse($invoice->InvoiceDate)->format('Y-m-d') : null,
                'due_date' => !empty($invoice->DueDate) ? \Carbon\Carbon::parse($invoice->DueDate)->format('Y-m-d') : null,
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
                        'due_date' => !empty($milestone?->PlannedDueDate) ? \Carbon\Carbon::parse($milestone->PlannedDueDate)->format('Y-m-d') : null,
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

                if ($grn && !empty($grn->GRNID)) {
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
                    'order_date' => !empty($po->OrderDate) ? \Carbon\Carbon::parse($po->OrderDate)->format('Y-m-d') : null,
                    'description' => $po->Description,
                    'before_tax' => $ordTotExcl,
                    'tax_percentage' => $taxPct,
                    'after_tax' => $ordTotIncl,
                ] : null,
                'grn' => $grn ? [
                    'id' => (int) $grn->id,
                    'grn_id' => $grn->GRNID,
                    'po_ref' => $grn->POID,
                    'received_date' => !empty($grn->ReceivedDate) ? \Carbon\Carbon::parse($grn->ReceivedDate)->format('Y-m-d') : null,
                    'ordered_qty_total' => (float) $grnItems->sum('po_qty'),
                    'received_qty_total' => (float) $grnItems->sum('received_qty'),
                    'items' => $grnItems,
                ] : null,
            ];
        }

        return $response;
    }

    private function numberToWords($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'forty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion',
        ];

        if (! is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'numberToWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );

            return false;
        }

        if ($number < 0) {
            return $negative . $this->numberToWords(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];

                break;
            case $number < 100:
                $tens = ((int) ($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }

                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . $this->numberToWords($remainder);
                }

                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int) ($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = $this->numberToWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= $this->numberToWords($remainder);
                }

                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = [];
            foreach (str_split((string) $fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }

    public function approve(Request $request, $id)
    {
        $voucher = FinanceVoucher::findOrFail($id);
        $voucher->ApprovalStatus = 'posted';
        // Optionally log reason: $request->input('reason')
        $voucher->Reasons = $request->Reasons;
        $voucher->save();

        return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Approved.');
    }

    public function reject(Request $request, $id)
    {
        $voucher = FinanceVoucher::findOrFail($id);
        $voucher->Status = 'rejected';
        $voucher->ApprovalStatus = 'rejected';
        // Optionally log reason: $request->input('reason')
        $voucher->Reasons = $request->Reasons;
        $voucher->save();

        return redirect()->route('paymentvoucher.index')->with('error', 'Voucher Rejected.');
    }

    public function applyContractPenalty(Request $request, int $invoiceId)
    {
        $this->authorizeContractExceptionAction();

        $invoice = FinanceInvoiceEntry::findOrFail($invoiceId);
        if (strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO')) !== 'CONTRACT') {
            return back()->with('error', 'Penalty can only be applied to contract invoices.');
        }
        if (!(bool) $invoice->IsOnHold && strtolower((string) ($invoice->MilestoneEligibilityStatus ?? '')) !== 'pending') {
            return back()->with('error', 'This invoice is not on a milestone hold.');
        }

        $validated = $request->validate([
            'penalty_amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        $penaltyAmount = (float) ($validated['penalty_amount'] ?? $invoice->PenaltySuggestedAmount ?? 0);
        if ($penaltyAmount <= 0) {
            return back()->with('error', 'Penalty amount must be greater than zero.');
        }

        ContractPenaltyEvent::create([
            'FinanceInvoiceID' => $invoice->Id,
            'ComputedAmount' => (float) ($invoice->PenaltySuggestedAmount ?? 0),
            'AppliedAmount' => $penaltyAmount,
            'Status' => 'Applied',
            'ActionBy' => Auth::id(),
            'ActionOn' => now(),
            'Reason' => $validated['reason'] ?? 'Penalty applied during voucher selection.',
        ]);

        $invoice->update([
            'IsOnHold' => false,
            'MilestoneEligibilityStatus' => 'Eligible',
            'HoldReason' => 'Released after penalty application.',
            'HoldSetBy' => Auth::id(),
            'HoldSetOn' => now(),
        ]);

        return back()->with('success', 'Penalty applied and invoice released for payment.');
    }

    public function waiveContractHold(Request $request, int $invoiceId)
    {
        $this->authorizeContractExceptionAction();

        $invoice = FinanceInvoiceEntry::findOrFail($invoiceId);
        if (strtoupper((string) ($invoice->InvoiceSourceType ?? 'PO')) !== 'CONTRACT') {
            return back()->with('error', 'Waiver is only available for contract invoices.');
        }
        if (!(bool) $invoice->IsOnHold && strtolower((string) ($invoice->MilestoneEligibilityStatus ?? '')) !== 'pending') {
            return back()->with('error', 'This invoice is not on a milestone hold.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        ContractPenaltyEvent::create([
            'FinanceInvoiceID' => $invoice->Id,
            'ComputedAmount' => (float) ($invoice->PenaltySuggestedAmount ?? 0),
            'AppliedAmount' => 0,
            'Status' => 'Waived',
            'ActionBy' => Auth::id(),
            'ActionOn' => now(),
            'Reason' => $validated['reason'],
        ]);

        $invoice->update([
            'IsOnHold' => false,
            'MilestoneEligibilityStatus' => 'Waived',
            'HoldReason' => 'Milestone hold waived: ' . $validated['reason'],
            'HoldSetBy' => Auth::id(),
            'HoldSetOn' => now(),
        ]);

        return back()->with('success', 'Contract hold waived and invoice released.');
    }

    private function authorizeContractExceptionAction(): void
    {
        try {
            $this->authorize(PermissionEnum::PaymentVoucherUpdate, FinanceVoucher::class);
        } catch (AuthorizationException $e) {
            $this->authorize(PermissionEnum::PaymentProcessingCreate, FinanceVoucher::class);
        }
    }

    public function destroy($id)
    {
        $this->authorize(PermissionEnum::PaymentVoucherDelete, FinanceVoucher::class);

        $voucher = FinanceVoucher::findOrFail($id);
        if ($voucher->ApprovalStatus !== 'draft') {
            return redirect()->route('paymentvoucher.index')->with('error', 'Only draft vouchers can be deleted.');
        }
        $voucher->DeletedBy = Auth::id();
        $voucher->save();
        $voucher->delete();

        activity()
            ->performedOn($voucher)
            ->causedBy(Auth::user())
            ->withProperties(['action' => 'delete'])
            ->log('Deleted Voucher Successfully' . $voucher->id);

        return redirect()->route('paymentvoucher.index')->with('success', 'Voucher Deleted Successfully');
    }
}
