<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\BankAccount;
use App\Models\Finance\Cashbook;
use App\Models\Finance\CashbookLine;
use App\Models\Finance\PettyCashFloat;
use App\Models\Finance\PettyCashLine;
use App\Models\Finance\PettyCashVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PettyCashController extends Controller
{
    // Suggest: reserve 1102600 for Petty Cash in t_Modules
    private const PETTYCASH_MODULE_ID = 1103700;

    public function index(Request $request)
    {
        // Filters for the grid
        $q = PettyCashVoucher::with(['float', 'currency'])->orderByDesc('VoucherID');
        if ($request->filled('FloatID')) {
            $q->where('FloatID', (int)$request->FloatID);
        }
        if ($request->filled('VoucherType')) {
            $q->where('VoucherType', $request->VoucherType);
        }
        if ($request->filled('Status')) {
            $q->where('Status', $request->Status);
        }
        $rows = $q->paginate(25)->appends($request->query());

        // Floats summary (widgets)
        $floats = PettyCashFloat::orderBy('Name')->get(['FloatID', 'Name', 'OpeningBalance', 'ReorderLevel', 'FloatLimit', 'CurrencyID']);
        $sumTopups = DB::table('t_PettyCashVouchers')
            ->select('FloatID', DB::raw('SUM(Amount) as s'))
            ->where('VoucherType', 'REPLENISHMENT')->where('Status', 'Posted')->groupBy('FloatID')->pluck('s', 'FloatID');
        $sumRefunds = DB::table('t_PettyCashVouchers')
            ->select('FloatID', DB::raw('SUM(Amount) as s'))
            ->where('VoucherType', 'REFUND')->where('Status', 'Posted')->groupBy('FloatID')->pluck('s', 'FloatID');
        $sumDisb = DB::table('t_PettyCashVouchers')
            ->select('FloatID', DB::raw('SUM(Amount) as s'))
            ->where('VoucherType', 'DISBURSEMENT')->where('Status', 'Posted')->groupBy('FloatID')->pluck('s', 'FloatID');

        // Pending reimbursement = Posted DISBURSEMENT without a batch
        $sumPending = DB::table('t_PettyCashVouchers')
            ->select('FloatID', DB::raw('SUM(Amount) as s'))
            ->where('VoucherType', 'DISBURSEMENT')->where('Status', 'Posted')
            ->whereNull('ReplenishmentBatchID')->groupBy('FloatID')->pluck('s', 'FloatID');

        // Build widgets data
        $widgets = [];
        foreach ($floats as $f) {
            $fid = $f->FloatID;
            $opening = (float)($f->OpeningBalance ?? 0);
            $topups = (float)($sumTopups[$fid] ?? 0);
            $disb = (float)($sumDisb[$fid] ?? 0);
            $refunds = (float)($sumRefunds[$fid] ?? 0);
            $balance = round($opening + $topups - $disb - $refunds, 2);
            $pending = round((float)($sumPending[$fid] ?? 0), 2);

            $widgets[] = [
                'FloatID' => $fid, 'Name' => $f->Name,
                'Balance' => $balance, 'Pending' => $pending,
                'ReorderLevel' => (float)($f->ReorderLevel ?? 0),
                'FloatLimit' => (float)($f->FloatLimit ?? 0),
            ];
        }

        return view('finance.pettycash.index', compact('rows', 'floats', 'widgets'));
    }

    public function createDisbursement()
    {
        $floats = PettyCashFloat::with('currency')->orderBy('Name')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name']);

        return view('finance.pettycash.create_disbursement', compact('floats', 'currencies'));
    }

    public function createReplenishment()
    {
        $floats = PettyCashFloat::with('currency')->orderBy('Name')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name']);
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();

        return view('finance.pettycash.create_replenishment', compact('floats', 'currencies', 'bankAccounts'));
    }

    public function createRefund()
    {
        $floats = PettyCashFloat::with('currency')->orderBy('Name')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name']);
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();

        return view('finance.pettycash.create_refund', compact('floats', 'currencies', 'bankAccounts'));
    }

    public function store(Request $request)
    {
        $type = $request->input('VoucherType');
        $rules = [
            'FloatID' => 'required|integer|exists:t_PettyCashFloats,FloatID',
            'VoucherType' => 'required|in:DISBURSEMENT,REPLENISHMENT,REFUND,ADJUSTMENT',
            'DocDate' => 'required|date',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'ExchangeRate' => 'nullable|numeric|min:0.000001',
            'Amount' => 'required|numeric|min:0.01',
            'Reference' => 'nullable|string|max:100',
            'Narration' => 'nullable|string|max:300',
        ];

        if (in_array($type, ['REPLENISHMENT', 'REFUND'])) {
            $rules['BankAccountID'] = 'required|integer|exists:t_BankAccounts,AccountID';
        }

        $data = $request->validate($rules);

        return DB::transaction(function () use ($data, $request, $type) {
            $v = new PettyCashVoucher($data);
            $float = PettyCashFloat::find($v->FloatID);
            if ($float && $float->RequireApproval && (float)$v->Amount > (float)$float->ApprovalLimit) {
                $v->ApprovalStatus = 'Pending';
            } else {
                $v->ApprovalStatus = 'N/A';
            }

            $v->Status = 'Draft';
            $v->save();

            // Lines for DISBURSEMENT / ADJUSTMENT
            $lines = $request->input('lines', []);
            foreach ($lines as $ln) {
                if (! empty($ln['Amount'])) {
                    PettyCashLine::create([
                        'VoucherID' => $v->VoucherID,
                        'GLAccountID' => $ln['GLAccountID'] ?? null,
                        'Description' => $ln['Description'] ?? null,
                        'Amount' => $ln['Amount'],
                    ]);
                }
            }

            return redirect()->route('finance.pettycash.show', $v->VoucherID)
                ->with('success', 'Petty cash voucher saved (Draft).');
        });
    }

    public function show($id)
    {
        $row = PettyCashVoucher::with(['float.currency', 'lines'])->findOrFail($id);

        return view('finance.pettycash.show', compact('row'));
    }

    public function post($id)
    {
        $v = PettyCashVoucher::with(['float', 'lines'])->findOrFail($id);
        if ($v->Status !== 'Draft') {
            return back()->with('error', 'Only Draft vouchers can be posted.');
        }

        // For REPLENISHMENT/REFUND, create a Cashbook entry to move funds between bank and petty cash (control GL via mapping/service).
        if ($v->VoucherType === 'REPLENISHMENT') {
            if (! $v->BankAccountID) {
                return back()->with('error', 'Bank account required for replenishment.');
            }
            $cb = new Cashbook([
                'EntryType' => 'PAYMENT', // bank pays out to petty cash
                'BankAccountID' => $v->BankAccountID,
                'DocDate' => $v->DocDate,
                'CurrencyID' => $v->CurrencyID,
                'ExchangeRate' => $v->ExchangeRate ?: 1,
                'Amount' => $v->Amount,
                'AmountBase' => $v->Amount,
                'Reference' => 'PC-TOPUP-' . $v->VoucherID,
                'Narration' => 'Petty cash top-up',
                'Status' => 'Posted',
                'SourceModule' => 'PETTYCASH',
                'SourceID' => $v->VoucherID,
                'IsSystemGenerated' => 1,
            ]);
            $cb->save();

            // Counter line (Credit) should be Petty Cash Control GL — if you prefer, fetch from mapping
            CashbookLine::create([
                'CashbookID' => $cb->CashbookID,
                'GLAccountID' => null, // set via mapping if you have FinanceGLMapping for PC_TOPUP
                'Description' => 'Petty Cash Control',
                'AmountDr' => 0,
                'AmountCr' => round((float)$v->Amount, 2),
            ]);

            $v->CashbookID = $cb->CashbookID;
        }

        if ($v->VoucherType === 'REFUND') {
            if (! $v->BankAccountID) {
                return back()->with('error', 'Bank account required for refund.');
            }
            $cb = new Cashbook([
                'EntryType' => 'RECEIPT', // bank receives from petty cash
                'BankAccountID' => $v->BankAccountID,
                'DocDate' => $v->DocDate,
                'CurrencyID' => $v->CurrencyID,
                'ExchangeRate' => $v->ExchangeRate ?: 1,
                'Amount' => $v->Amount,
                'AmountBase' => $v->Amount,
                'Reference' => 'PC-REFUND-' . $v->VoucherID,
                'Narration' => 'Petty cash refund',
                'Status' => 'Posted',
                'SourceModule' => 'PETTYCASH',
                'SourceID' => $v->VoucherID,
                'IsSystemGenerated' => 1,
            ]);
            $cb->save();

            CashbookLine::create([
                'CashbookID' => $cb->CashbookID,
                'GLAccountID' => null, // map to Petty Cash Control
                'Description' => 'Petty Cash Control',
                'AmountDr' => round((float)$v->Amount, 2),
                'AmountCr' => 0,
            ]);

            $v->CashbookID = $cb->CashbookID;
        }

        // TODO: for DISBURSEMENT, post lines to GL (Debit each line GL; Credit Petty Cash Control). Hook into your GL posting service / mapping.

        $v->Status = 'Posted';
        $v->PostedOn = now();
        $v->PostedBy = auth()->id();
        $v->save();

        return redirect()->route('finance.pettycash.show', $v->VoucherID)->with('success', 'Voucher posted.');
    }

    public function void($id)
    {
        $v = PettyCashVoucher::findOrFail($id);
        if ($v->Status !== 'Posted') {
            return back()->with('error', 'Only Posted vouchers can be voided.');
        }

        // TODO: reverse GL as needed (and Cashbook if created).
        $v->Status = 'Voided';
        $v->VoidedOn = now();
        $v->VoidedBy = auth()->id();
        $v->save();

        return redirect()->route('finance.pettycash.show', $v->VoucherID)->with('success', 'Voucher voided.');
    }

    public function destroy($id)
    {
        $v = PettyCashVoucher::with('lines')->findOrFail($id);
        if ($v->Status === 'Posted') {
            return back()->with('error', 'Posted vouchers cannot be deleted.');
        }
        $v->lines()->delete();
        $v->delete();

        return redirect()->route('finance.pettycash.index')->with('success', 'Voucher deleted.');
    }

    public function submitForApproval($id)
    {
        $v = PettyCashVoucher::findOrFail($id);
        if ($v->Status !== 'Draft') {
            return back()->with('error', 'Only Draft vouchers can be submitted.');
        }
        $v->ApprovalStatus = 'Pending';
        $v->SubmittedOn = now();
        $v->SubmittedBy = auth()->id();
        $v->save();

        return back()->with('success', 'Submitted for approval.');
    }

    public function approve($id)
    {
        $v = PettyCashVoucher::with('float')->findOrFail($id);
        if ($v->ApprovalStatus !== 'Pending') {
            return back()->with('error', 'Voucher is not pending approval.');
        }
        $v->ApprovalStatus = 'Approved';
        $v->ApprovedOn = now();
        $v->ApprovedBy = auth()->id();
        $v->save();

        return back()->with('success', 'Voucher approved.');
    }

    public function reject($id)
    {
        $v = PettyCashVoucher::findOrFail($id);
        if ($v->ApprovalStatus !== 'Pending') {
            return back()->with('error', 'Voucher is not pending approval.');
        }
        $v->ApprovalStatus = 'Rejected';
        $v->save();

        return back()->with('success', 'Voucher rejected.');
    }

    public function wizard()
    {
        $floats = PettyCashFloat::orderBy('Name')->get(['FloatID', 'Name']);
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();

        return view('finance.pettycash.wizard_replenishment', compact('floats', 'bankAccounts'));
    }

    public function wizardPreview(Request $request)
    {
        $data = $request->validate([
            'FloatID' => 'required|integer|exists:t_PettyCashFloats,FloatID',
        ]);

        $items = PettyCashVoucher::where('FloatID', $data['FloatID'])
            ->where('VoucherType', 'DISBURSEMENT')->where('Status', 'Posted')
            ->whereNull('ReplenishmentBatchID')
            ->orderBy('DocDate')->get(['VoucherID', 'DocDate', 'Amount', 'Reference', 'Narration']);

        return response()->json([
            'total' => round((float)$items->sum('Amount'), 2),
            'items' => $items,
        ]);
    }

    public function wizardStore(Request $request)
    {
        $data = $request->validate([
            'FloatID' => 'required|integer|exists:t_PettyCashFloats,FloatID',
            'BankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'BatchDate' => 'required|date',
            'voucher_ids' => 'required|array|min:1',
            'voucher_ids.*' => 'integer|exists:t_PettyCashVouchers,VoucherID',
        ]);

        return DB::transaction(function () use ($data) {
            // Compute total from selected vouchers (safe-guard and lock them)
            $vouchers = PettyCashVoucher::lockForUpdate()
                ->whereIn('VoucherID', $data['voucher_ids'])
                ->where('VoucherType', 'DISBURSEMENT')->where('Status', 'Posted')
                ->whereNull('ReplenishmentBatchID')
                ->get();

            if ($vouchers->isEmpty()) {
                return back()->with('error', 'No eligible vouchers found.')->withInput();
            }
            $total = (float)$vouchers->sum('Amount');

            // Create batch
            $batch = \App\Models\Finance\PettyCashReplenishmentBatch::create([
                'FloatID' => (int)$data['FloatID'],
                'BankAccountID' => (int)$data['BankAccountID'],
                'BatchDate' => $data['BatchDate'],
                'TotalAmount' => round($total, 2),
                'Status' => 'Draft',
            ]);

            // Create replenishment voucher (header only; cashbook posting on "post")
            $rv = PettyCashVoucher::create([
                'FloatID' => (int)$data['FloatID'],
                'VoucherType' => 'REPLENISHMENT',
                'DocDate' => $data['BatchDate'],
                'CurrencyID' => $vouchers->first()->CurrencyID, // assuming same
                'ExchangeRate' => 1,
                'Amount' => round($total, 2),
                'Status' => 'Draft',
                'BankAccountID' => (int)$data['BankAccountID'],
                'Reference' => 'PC-BATCH-' . $batch->BatchID,
                'Narration' => 'Replenishment for posted disbursements',
                'ReplenishmentBatchID' => $batch->BatchID,
            ]);

            // Link the disbursement vouchers to this batch
            PettyCashVoucher::whereIn('VoucherID', $vouchers->pluck('VoucherID'))
                ->update(['ReplenishmentBatchID' => $batch->BatchID]);

            return redirect()->route('finance.pettycash.show', $rv->VoucherID)
                ->with('success', 'Batch prepared; review and post the replenishment voucher.');
        });
    }
}
