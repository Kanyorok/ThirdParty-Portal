<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\BankAccount;
use App\Models\Finance\BankTransfer;
use App\Models\Finance\Cashbook;
use App\Models\Finance\CashbookLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankTransferController extends Controller
{
    // Default clearing GL (override in form or config)
    private const DEFAULT_CLEARING_GL = 101999;

    public function index()
    {
        $rows = BankTransfer::with(['fromAccount.bank', 'toAccount.bank', 'currency'])
            ->orderByDesc('TransferID')->paginate(25);
        return view('finance.bank-transfers.index', compact('rows'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'DecimalDigits']);
        $defaults = [
            'DocDate' => now()->toDateString(),
            'ExchangeRate' => 1,
            'ClearingGLAccountID' => config('finance.gl.interbank_clearing', self::DEFAULT_CLEARING_GL),
        ];
        return view('finance.bank-transfers.create', compact('bankAccounts', 'currencies', 'defaults'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'FromBankAccountID' => 'required|different:ToBankAccountID|integer|exists:t_BankAccounts,AccountID',
            'ToBankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'DocDate' => 'required|date',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'ExchangeRate' => 'nullable|numeric|min:0',
            'Amount' => 'required|numeric|min:0.01',
            'ClearingGLAccountID' => 'nullable|integer',
            'Reference' => 'nullable|string|max:100',
            'Narration' => 'nullable|string|max:300',
        ]);

        return DB::transaction(function () use ($request) {
            $data = $request->only([
                'FromBankAccountID', 'ToBankAccountID', 'DocDate', 'CurrencyID',
                'ExchangeRate', 'Amount', 'ClearingGLAccountID', 'Reference', 'Narration'
            ]);
            $data['ExchangeRate'] = $data['ExchangeRate'] ?: 1;
            $data['AmountBase'] = round($data['Amount'] * $data['ExchangeRate'], 2);
            $data['Status'] = 'Draft';
            $data['ClearingGLAccountID'] = $data['ClearingGLAccountID']
                ?: (int)config('finance.gl.interbank_clearing', self::DEFAULT_CLEARING_GL);

            $transfer = BankTransfer::create($data);

            return redirect()->route('banktransfers.show', $transfer->TransferID)
                ->with('success', 'Transfer saved (Draft).');
        });
    }

    public function show($id)
    {
        $row = BankTransfer::with(['fromAccount.bank', 'toAccount.bank', 'currency'])->findOrFail($id);
        return view('finance.bank-transfers.show', compact('row'));
    }

    public function edit($id)
    {
        $row = BankTransfer::findOrFail($id);
        if ($row->Status !== 'Draft') {
            return redirect()->route('banktransfers.show', $row->TransferID)->with('error', 'Only Draft can be edited.');
        }
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'DecimalDigits']);
        return view('finance.bank-transfers.edit', compact('row', 'bankAccounts', 'currencies'));
    }

    public function update(Request $request, $id)
    {
        $row = BankTransfer::findOrFail($id);
        if ($row->Status !== 'Draft') {
            return redirect()->route('banktransfers.show', $row->TransferID)->with('error', 'Only Draft can be updated.');
        }

        $request->validate([
            'FromBankAccountID' => 'required|different:ToBankAccountID|integer|exists:t_BankAccounts,AccountID',
            'ToBankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'DocDate' => 'required|date',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'ExchangeRate' => 'nullable|numeric|min:0',
            'Amount' => 'required|numeric|min:0.01',
            'ClearingGLAccountID' => 'nullable|integer',
            'Reference' => 'nullable|string|max:100',
            'Narration' => 'nullable|string|max:300',
        ]);

        return DB::transaction(function () use ($request, $row) {
            $row->fill($request->only([
                'FromBankAccountID', 'ToBankAccountID', 'DocDate', 'CurrencyID',
                'ExchangeRate', 'Amount', 'ClearingGLAccountID', 'Reference', 'Narration'
            ]));
            $row->ExchangeRate = $row->ExchangeRate ?: 1;
            $row->AmountBase = round($row->Amount * $row->ExchangeRate, 2);
            $row->save();

            return redirect()->route('banktransfers.show', $row->TransferID)
                ->with('success', 'Transfer updated.');
        });
    }

    public function destroy($id)
    {
        $row = BankTransfer::findOrFail($id);
        if ($row->Status === 'Posted') {
            return back()->with('error', 'Posted transfers cannot be deleted.');
        }
        $row->delete();
        return redirect()->route('banktransfers.index')->with('success', 'Transfer deleted.');
    }

    public function post($id)
    {
        $t = BankTransfer::with(['fromAccount', 'toAccount'])->findOrFail($id);
        if ($t->Status !== 'Draft') {
            return back()->with('error', 'Only Draft transfers can be posted.');
        }

        // Sanity
        if ((int)$t->FromBankAccountID === (int)$t->ToBankAccountID) {
            return back()->with('error', 'From and To accounts must be different.');
        }

        return DB::transaction(function () use ($t) {
            $clearing = (int)($t->ClearingGLAccountID ?: config('finance.gl.interbank_clearing', self::DEFAULT_CLEARING_GL));
            $amount = (float)$t->Amount;

            // 1) Create PAYMENT cashbook on From account (bank CR, counter DR clearing)
            $cbPay = new Cashbook([
                'EntryType' => 'PAYMENT',
                'BankAccountID' => $t->FromBankAccountID,
                'DocDate' => $t->DocDate,
                'CurrencyID' => $t->CurrencyID,
                'ExchangeRate' => $t->ExchangeRate,
                'Amount' => $t->Amount,
                'AmountBase' => $t->AmountBase,
                'Reference' => 'XFER-' . $t->TransferID,
                'Narration' => 'Transfer to ' . $t->toAccount?->AccountNumber,
                'Status' => 'Posted',          // post immediately as part of transfer
                'SourceModule' => 'BANK_TRANSFER',
                'SourceID' => $t->TransferID,
                'IsSystemGenerated' => 1,
            ]);
            $cbPay->save();
            CashbookLine::create([
                'CashbookID' => $cbPay->CashbookID,
                'GLAccountID' => $clearing,
                'Description' => 'Interbank Clearing (Payment)',
                'AmountDr' => round($amount, 2),
                'AmountCr' => 0,
            ]);

            // 2) Create RECEIPT cashbook on To account (bank DR, counter CR clearing)
            $cbRec = new Cashbook([
                'EntryType' => 'RECEIPT',
                'BankAccountID' => $t->ToBankAccountID,
                'DocDate' => $t->DocDate,
                'CurrencyID' => $t->CurrencyID,
                'ExchangeRate' => $t->ExchangeRate,
                'Amount' => $t->Amount,
                'AmountBase' => $t->AmountBase,
                'Reference' => 'XFER-' . $t->TransferID,
                'Narration' => 'Transfer from ' . $t->fromAccount?->AccountNumber,
                'Status' => 'Posted',
                'SourceModule' => 'BANK_TRANSFER',
                'SourceID' => $t->TransferID,
                'IsSystemGenerated' => 1,
            ]);
            $cbRec->save();
            CashbookLine::create([
                'CashbookID' => $cbRec->CashbookID,
                'GLAccountID' => $clearing,
                'Description' => 'Interbank Clearing (Receipt)',
                'AmountDr' => 0,
                'AmountCr' => round($amount, 2),
            ]);

            // Mark transfer posted
            $t->Status = 'Posted';
            $t->save();

            return redirect()->route('banktransfers.show', $t->TransferID)->with('success', 'Transfer posted and cashbook entries created.');
        });
    }

    public function void($id)
    {
        $t = BankTransfer::findOrFail($id);
        if ($t->Status !== 'Posted') {
            return back()->with('error', 'Only Posted transfers can be voided.');
        }
        // TODO: create reversing journals (and/or reversing cashbook) as per your GL policy
        $t->Status = 'Voided';
        $t->save();

        return redirect()->route('banktransfers.show', $t->TransferID)->with('success', 'Transfer voided (remember to reverse in GL if required).');
    }
}
