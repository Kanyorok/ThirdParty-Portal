<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Models\Finance\Bank;
use App\Models\Finance\BankBranch;
use App\Models\Finance\BankAccount;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceGLAccounts;
use Illuminate\Http\Request;

class BankAccountSetupController extends Controller
{
    public function index()
    {
        $accounts = BankAccount::with(['bank', 'branch', 'glAccount'])
            ->orderBy('AccountNumber')
            ->paginate(20);

        return view('finance.bankaccountsetup.index', compact('accounts'));
    }

    public function create()
    {
        $banks = Bank::orderBy('BankName')->get(['BankID', 'BankName']);
        $branches = BankBranch::orderBy('BranchName')->get(['BranchID', 'BankID', 'BranchName']);
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'Symbol', 'DecimalDigits']);
        $glAccounts = FinanceGLAccounts::where('IsActive', 1)
            ->orderBy('GLName')
            ->get(['Id', 'GLCode', 'GLName', 'Description']);

        return view('finance.bankaccountsetup.create', compact('banks', 'branches', 'currencies', 'glAccounts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'BankID' => 'required|integer|exists:t_Banks,BankID',
            'BranchID' => 'nullable|integer|exists:t_BankBranches,BranchID',
            'AccountName' => 'nullable|string|max:150',
            'AccountNumber' => 'required|string|max:50',
            'IBAN' => 'nullable|string|max:34',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'GLAccountID' => 'nullable|integer',
            'OpeningBalance' => 'nullable|numeric',
            'IsDefault' => 'nullable|boolean',
            'IsActive' => 'nullable|boolean',
        ]);

        $acc = new BankAccount($request->only([
            'BankID', 'BranchID', 'AccountName', 'AccountNumber', 'IBAN',
            'CurrencyID', 'GLAccountID', 'OpeningBalance', 'IsDefault', 'IsActive'
        ]));
        // CurrentBalance starts at OpeningBalance
        $acc->CurrentBalance = $request->input('OpeningBalance', 0);
        $acc->IsDefault = $request->boolean('IsDefault');
        $acc->IsActive = $request->boolean('IsActive', true);
        $acc->save();

        return redirect()->route('finance.bankaccountsetup.index')
            ->with('success', 'Bank account created.');
    }

    public function show($id)
    {
        $account = BankAccount::with(['bank', 'branch'])->findOrFail($id);
        return view('finance.bankaccountsetup.show', compact('account'));
    }

    public function edit($id)
    {
        $account = BankAccount::findOrFail($id);
        $banks = Bank::orderBy('BankName')->get(['BankID', 'BankName']);
        $branches = BankBranch::orderBy('BranchName')->get(['BranchID', 'BankID', 'BranchName']);
        $currencies = Currency::orderBy('Name')->get(['Id', 'Code', 'Name', 'Symbol', 'DecimalDigits']);
        $glAccounts = FinanceGLAccounts::where('IsActive', 1)
            ->orderBy('GLName')
            ->get(['Id', 'GLCode', 'GLName', 'Description']);

        return view('finance.bankaccountsetup.edit', compact('account', 'banks', 'branches', 'currencies', 'glAccounts'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'BankID' => 'required|integer|exists:t_Banks,BankID',
            'BranchID' => 'nullable|integer|exists:t_BankBranches,BranchID',
            'AccountName' => 'nullable|string|max:150',
            'AccountNumber' => 'required|string|max:50',
            'IBAN' => 'nullable|string|max:34',
            'CurrencyID' => 'required|integer|exists:t_Currencies,Id',
            'GLAccountID' => 'nullable|integer',
            'IsDefault' => 'nullable|boolean',
            'IsActive' => 'nullable|boolean',
            // OpeningBalance usually immutable after go-live; keep optional:
            'OpeningBalance' => 'nullable|numeric',
        ]);

        $acc = BankAccount::findOrFail($id);
        $acc->fill($request->only([
            'BankID', 'BranchID', 'AccountName', 'AccountNumber', 'IBAN',
            'CurrencyID', 'GLAccountID', 'IsDefault', 'IsActive'
        ]));
        // If OpeningBalance provided, you can decide policy; here we won't change CurrentBalance
        if ($request->filled('OpeningBalance')) {
            $acc->OpeningBalance = $request->OpeningBalance;
        }
        $acc->IsDefault = $request->boolean('IsDefault');
        $acc->IsActive = $request->boolean('IsActive', true);
        $acc->save();

        return redirect()->route('finance.bankaccountsetup.index')
            ->with('success', 'Bank account updated.');
    }

    public function destroy($id)
    {
        $acc = BankAccount::findOrFail($id);
        $acc->delete();

        return redirect()->route('finance.bankaccountsetup.index')
            ->with('success', 'Bank account deleted.');
    }
}
