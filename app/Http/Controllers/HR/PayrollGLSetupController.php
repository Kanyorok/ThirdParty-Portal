<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Core\Currency;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\HR\PayrollGLSetting;
use Illuminate\Http\Request;

class PayrollGLSetupController extends Controller
{
    public function edit()
    {
        $settings = PayrollGLSetting::orderByDesc('Id')->first();
        $glAccounts = FinanceGLAccounts::where('IsActive', 1)
            ->where('IsPostingAccount', 1)
            ->orderBy('GLCode')
            ->get(['Id','GLCode','GLName','CBSAccountCode']);
        $currencies = Currency::orderBy('Code')->get(['Id','Code','Name','Symbol']);

        return view('hr.payroll.glsetup.edit', compact('settings', 'glAccounts', 'currencies'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'PayrollControlGLAccountID' => ['required','integer','exists:t_FinanceGLAccounts,Id'],
            'BasicSalaryExpenseGLAccountID' => ['required','integer','exists:t_FinanceGLAccounts,Id'],
            'SalaryBankGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'SalaryCBSSettlementGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'GratuityExpenseGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'GratuityLiabilityGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'CurrencyID' => ['nullable','integer','exists:t_Currencies,Id'],
        ]);

        $settings = PayrollGLSetting::orderByDesc('Id')->first();
        $now = now();
        if ($settings) {
            $settings->update(array_merge($data, [
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => $now,
            ]));
        } else {
            PayrollGLSetting::create(array_merge($data, [
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => $now,
            ]));
        }

        return redirect()->route('hr.payroll.glsetup.edit')->with('success', 'Payroll GL setup saved.');
    }
}
