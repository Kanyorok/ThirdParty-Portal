<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\HR\PayrollDeduction;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayrollDeductionController extends Controller
{
    public function index()
    {
        $deductions = PayrollDeduction::orderBy('Name')->paginate(20);
        return view('hr.statutory.deductions.index', compact('deductions'));
    }

    public function create()
    {
        $glAccounts = FinanceGLAccounts::where('IsActive', 1)
            ->where('IsPostingAccount', 1)
            ->orderBy('GLCode')
            ->get(['Id','GLCode','GLName','CBSAccountCode']);

        return view('hr.statutory.deductions.create', compact('glAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRPayrollDeductions,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'DebitGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'CreditGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'EmployerContributionEnabled' => ['nullable', 'boolean'],
            'EmployerCalcMethod' => ['nullable', 'string', 'max:50'],
            'EmployerRate' => ['nullable', 'numeric', 'min:0'],
            'EmployerAmount' => ['nullable', 'numeric', 'min:0'],
            'EmployerDebitGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'EmployerCreditGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'IsMandatory' => ['nullable', 'boolean'],
            'ShowInPayslip' => ['nullable', 'boolean'],
            'IsTaxAllowable' => ['nullable', 'boolean'],
            'ApplyFor' => ['nullable', 'string', 'max:50'],
        ]);

        $data['IsActive'] = 1;
        $data['IsMandatory'] = $request->boolean('IsMandatory');
        $data['ShowInPayslip'] = $request->boolean('ShowInPayslip', true);
        $data['IsTaxAllowable'] = $request->boolean('IsTaxAllowable');
        $data['EmployerContributionEnabled'] = $request->boolean('EmployerContributionEnabled');
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        PayrollDeduction::create($data);

        return redirect()->route('hr.statutory.deductions.index')->with('success', 'Deduction created.');
    }

    public function edit($id)
    {
        $deduction = PayrollDeduction::findOrFail($id);
        $glAccounts = FinanceGLAccounts::where('IsActive', 1)
            ->where('IsPostingAccount', 1)
            ->orderBy('GLCode')
            ->get(['Id','GLCode','GLName','CBSAccountCode']);

        return view('hr.statutory.deductions.edit', compact('deduction','glAccounts'));
    }

    public function update(Request $request, $id)
    {
        $deduction = PayrollDeduction::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRPayrollDeductions', 'Code')->ignore($deduction->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'DebitGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'CreditGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'EmployerContributionEnabled' => ['nullable', 'boolean'],
            'EmployerCalcMethod' => ['nullable', 'string', 'max:50'],
            'EmployerRate' => ['nullable', 'numeric', 'min:0'],
            'EmployerAmount' => ['nullable', 'numeric', 'min:0'],
            'EmployerDebitGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'EmployerCreditGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'IsActive' => ['nullable', 'boolean'],
            'IsMandatory' => ['nullable', 'boolean'],
            'ShowInPayslip' => ['nullable', 'boolean'],
            'IsTaxAllowable' => ['nullable', 'boolean'],
            'ApplyFor' => ['nullable', 'string', 'max:50'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $deduction->IsActive;
        $data['IsMandatory'] = $request->boolean('IsMandatory', $deduction->IsMandatory);
        $data['ShowInPayslip'] = $request->boolean('ShowInPayslip', $deduction->ShowInPayslip);
        $data['IsTaxAllowable'] = $request->boolean('IsTaxAllowable', $deduction->IsTaxAllowable);
        $data['EmployerContributionEnabled'] = $request->boolean('EmployerContributionEnabled', $deduction->EmployerContributionEnabled);
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $deduction->update($data);

        return redirect()->route('hr.statutory.deductions.index')->with('success', 'Deduction updated.');
    }

    public function destroy($id)
    {
        $deduction = PayrollDeduction::findOrFail($id);
        $deduction->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.deductions.index')->with('success', 'Deduction deactivated.');
    }

    public function activate($id)
    {
        $deduction = PayrollDeduction::findOrFail($id);
        $deduction->update([
            'IsActive' => 1,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.deductions.index')->with('success', 'Deduction activated.');
    }
}
