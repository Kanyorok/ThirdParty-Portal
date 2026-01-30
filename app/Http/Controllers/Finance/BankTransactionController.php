<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\BankAccount;
use App\Models\Finance\BankTransaction;
use App\Models\Finance\Cashbook;
use App\Models\Finance\CashbookLine;
use App\Models\Finance\FinanceGLMapping;
use App\Models\Finance\FinanceTransactionTypes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BankTransactionController extends Controller
{
    // Adjust to your t_Modules ModuleID for Bank Transactions
    private const BANK_TXN_MODULE_ID = 1102500;
    // Fallback to Cashbook mappings if none defined for Bank Txn module
    private const CASHBOOK_MODULE_ID_FALL = 1102400;

    public function index()
    {
        $rows = BankTransaction::with(['bankAccount.bank', 'currency', 'txnType'])
            ->orderByDesc('BankTxnID')
            ->paginate(25);

        return view('finance.bank-transactions.index', compact('rows'));
    }

    public function create()
    {
        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'DecimalDigits']);

        // Only show types that have a mapping (module or fallback)
        $mappedNow = FinanceGLMapping::whereIn('ModuleID', [self::BANK_TXN_MODULE_ID, self::CASHBOOK_MODULE_ID_FALL])
            ->where('IsActive', 1)->pluck('TransactionTypeID')->unique()->filter();

        $txnTypes = FinanceTransactionTypes::where('IsActive', 1)
            ->whereIn('Id', $mappedNow)->orderBy('Name')
            ->get(['Id', 'Code', 'Name', 'Description']);

        $defaults = [
            'DocDate' => now()->toDateString(),
            'ExchangeRate' => 1,
        ];

        return view('finance.bank-transactions.create', compact('bankAccounts', 'currencies', 'txnTypes', 'defaults'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'BankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'TransactionTypeID' => 'required|integer|exists:t_FinanceTransactionTypes,Id',
            'DocDate' => 'required|date',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'ExchangeRate' => 'nullable|numeric|min:0',
            'Amount' => 'required|numeric|min:0.01',
            'Reference' => 'nullable|string|max:100',
            'Narration' => 'nullable|string|max:300',
        ]);

        return DB::transaction(function () use ($request) {
            $data = $request->only([
                'BankAccountID', 'TransactionTypeID', 'DocDate', 'CurrencyID',
                'ExchangeRate', 'Amount', 'Reference', 'Narration',
            ]);
            $data['ExchangeRate'] = $data['ExchangeRate'] ?: 1;
            $data['AmountBase'] = round($data['Amount'] * $data['ExchangeRate'], 2);
            $data['Status'] = 'Draft';

            $row = BankTransaction::create($data);

            return redirect()->route('finance.banktransactions.show', $row->BankTxnID)
                ->with('success', 'Bank transaction saved (Draft).');
        });
    }

    public function show($id)
    {
        $row = BankTransaction::with(['bankAccount.bank', 'currency', 'txnType'])->findOrFail($id);

        return view('finance.bank-transactions.show', compact('row'));
    }

    public function edit($id)
    {
        $row = BankTransaction::findOrFail($id);
        if ($row->Status !== 'Draft') {
            return redirect()->route('finance.banktransactions.show', $row->BankTxnID)->with('error', 'Only Draft can be edited.');
        }

        $bankAccounts = BankAccount::with('bank')->orderBy('AccountNumber')->get();
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'DecimalDigits']);

        $mappedNow = FinanceGLMapping::whereIn('ModuleID', [self::BANK_TXN_MODULE_ID, self::CASHBOOK_MODULE_ID_FALL])
            ->where('IsActive', 1)->pluck('TransactionTypeID')->unique()->filter();

        $txnTypes = FinanceTransactionTypes::where('IsActive', 1)
            ->whereIn('Id', $mappedNow)->orderBy('Name')
            ->get(['Id', 'Code', 'Name', 'Description']);

        return view('finance.bank-transactions.edit', compact('row', 'bankAccounts', 'currencies', 'txnTypes'));
    }

    public function update(Request $request, $id)
    {
        $row = BankTransaction::findOrFail($id);
        if ($row->Status !== 'Draft') {
            return redirect()->route('finance.banktransactions.show', $row->BankTxnID)->with('error', 'Only Draft can be updated.');
        }

        $request->validate([
            'BankAccountID' => 'required|integer|exists:t_BankAccounts,AccountID',
            'TransactionTypeID' => 'required|integer|exists:t_FinanceTransactionTypes,Id',
            'DocDate' => 'required|date',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'ExchangeRate' => 'nullable|numeric|min:0',
            'Amount' => 'required|numeric|min:0.01',
            'Reference' => 'nullable|string|max:100',
            'Narration' => 'nullable|string|max:300',
        ]);

        return DB::transaction(function () use ($request, $row) {
            $row->fill($request->only([
                'BankAccountID', 'TransactionTypeID', 'DocDate', 'CurrencyID',
                'ExchangeRate', 'Amount', 'Reference', 'Narration',
            ]));
            $row->ExchangeRate = $row->ExchangeRate ?: 1;
            $row->AmountBase = round($row->Amount * $row->ExchangeRate, 2);
            $row->save();

            return redirect()->route('finance.banktransactions.show', $row->BankTxnID)
                ->with('success', 'Bank transaction updated.');
        });
    }

    public function destroy($id)
    {
        $row = BankTransaction::findOrFail($id);
        if ($row->Status === 'Posted') {
            return back()->with('error', 'Posted bank transactions cannot be deleted.');
        }
        $row->delete();

        return redirect()->route('finance.banktransactions.index')->with('success', 'Bank transaction deleted.');
    }

    public function post($id)
    {
        $bt = BankTransaction::with(['bankAccount', 'txnType'])->findOrFail($id);
        if ($bt->Status !== 'Draft') {
            return back()->with('error', 'Only Draft transactions can be posted.');
        }

        // Find mapping (module first, else fallback to cashbook)
        $map = FinanceGLMapping::where('ModuleID', self::BANK_TXN_MODULE_ID)
            ->where('TransactionTypeID', $bt->TransactionTypeID)
            ->where('IsActive', 1)
            ->first();

        if (! $map) {
            $map = FinanceGLMapping::where('ModuleID', self::CASHBOOK_MODULE_ID_FALL)
                ->where('TransactionTypeID', $bt->TransactionTypeID)
                ->where('IsActive', 1)
                ->first();
        }

        if (! $map) {
            return back()->with('error', 'No GL Mapping found for this Transaction Type.');
        }

        // Decide Receipt vs Payment from mapping
        $entryType = $map->CreditGLAccountID ? 'RECEIPT' : 'PAYMENT';
        $counterGL = $map->CreditGLAccountID ?: $map->DebitGLAccountID;

        if (! $counterGL) {
            return back()->with('error', 'Mapping has no Debit/Credit GL configured.');
        }

        return DB::transaction(function () use ($bt, $entryType, $counterGL) {
            // Create and post a Cashbook entry
            $cb = new Cashbook([
                'EntryType' => $entryType,
                'BankAccountID' => $bt->BankAccountID,
                'DocDate' => $bt->DocDate,
                'CurrencyID' => $bt->CurrencyID,
                'ExchangeRate' => $bt->ExchangeRate,
                'Amount' => $bt->Amount,
                'AmountBase' => $bt->AmountBase,
                'Reference' => 'BTX-' . $bt->BankTxnID,
                'Narration' => $bt->Narration ?: $bt->txnType?->Name,
                'Status' => 'Posted',
                'SourceModule' => 'BANK_TRANSACTION',
                'SourceID' => $bt->BankTxnID,
                'IsSystemGenerated' => 1,
            ]);
            $cb->save();

            // Counter line based on entry type
            if ($entryType === 'RECEIPT') {
                CashbookLine::create([
                    'CashbookID' => $cb->CashbookID,
                    'GLAccountID' => $counterGL,
                    'Description' => $bt->txnType?->Name,
                    'AmountDr' => 0,
                    'AmountCr' => round((float)$bt->Amount, 2),
                ]);
            } else { // PAYMENT
                CashbookLine::create([
                    'CashbookID' => $cb->CashbookID,
                    'GLAccountID' => $counterGL,
                    'Description' => $bt->txnType?->Name,
                    'AmountDr' => round((float)$bt->Amount, 2),
                    'AmountCr' => 0,
                ]);
            }

            // Mark BT posted
            $bt->Status = 'Posted';
            $bt->save();

            return redirect()->route('finance.banktransactions.show', $bt->BankTxnID)->with('success', 'Bank transaction posted via Cashbook.');
        });
    }

    public function void($id)
    {
        $bt = BankTransaction::findOrFail($id);
        if ($bt->Status !== 'Posted') {
            return back()->with('error', 'Only Posted transactions can be voided.');
        }
        // TODO: reverse GL according to your policy
        $bt->Status = 'Voided';
        $bt->save();

        return redirect()->route('finance.banktransactions.show', $bt->BankTxnID)->with('success', 'Bank transaction voided (remember GL reversal if needed).');
    }
}
