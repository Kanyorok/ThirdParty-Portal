<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Finance\FinanceGLAccounts;
use App\Models\HR\JobGrade;
use App\Models\HR\PayrollAllowance;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayrollAllowanceController extends Controller
{
    public function index()
    {
        $allowances = PayrollAllowance::orderBy('Name')->paginate(20);

        return view('hr.statutory.allowances.index', compact('allowances'));
    }

    public function create()
    {
        $grades = JobGrade::orderBy('Name')->get();
        $glAccounts = FinanceGLAccounts::where('IsActive', 1)
            ->where('IsPostingAccount', 1)
            ->orderBy('GLCode')
            ->get(['Id','GLCode','GLName','CBSAccountCode']);

        return view('hr.statutory.allowances.create', compact('grades', 'glAccounts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRPayrollAllowances,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'DebitGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'CreditGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'IsTaxable' => ['nullable', 'boolean'],
            'IsPensionable' => ['nullable', 'boolean'],
            'IsMandatory' => ['nullable','boolean'],
            'Grades' => ['array'],
            'Grades.*' => ['integer','exists:t_HRJobGrades,Id'],
        ]);

        $data['IsTaxable'] = $request->boolean('IsTaxable', true);
        $data['IsPensionable'] = $request->boolean('IsPensionable', false);
        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        $allowance = PayrollAllowance::create($data);
        $allowance->grades()->sync($request->input('Grades', []));

        return redirect()->route('hr.statutory.allowances.index')->with('success', 'Allowance created.');
    }

    public function edit($id)
    {
        $allowance = PayrollAllowance::findOrFail($id);
        $grades = JobGrade::orderBy('Name')->get();
        $glAccounts = FinanceGLAccounts::where('IsActive', 1)
            ->where('IsPostingAccount', 1)
            ->orderBy('GLCode')
            ->get(['Id','GLCode','GLName','CBSAccountCode']);
        $selectedGrades = $allowance->grades()->pluck('t_HRJobGrades.Id')->toArray();

        return view('hr.statutory.allowances.edit', compact('allowance', 'grades', 'selectedGrades', 'glAccounts'));
    }

    public function update(Request $request, $id)
    {
        $allowance = PayrollAllowance::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRPayrollAllowances', 'Code')->ignore($allowance->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'DebitGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'CreditGLAccountID' => ['nullable','integer','exists:t_FinanceGLAccounts,Id'],
            'IsTaxable' => ['nullable', 'boolean'],
            'IsPensionable' => ['nullable', 'boolean'],
            'IsActive' => ['nullable', 'boolean'],
            'IsMandatory' => ['nullable','boolean'],
            'Grades' => ['array'],
            'Grades.*' => ['integer','exists:t_HRJobGrades,Id'],
        ]);

        $data['IsTaxable'] = $request->boolean('IsTaxable', $allowance->IsTaxable);
        $data['IsPensionable'] = $request->boolean('IsPensionable', $allowance->IsPensionable);
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $allowance->IsActive;
        $data['IsMandatory'] = $request->boolean('IsMandatory', $allowance->IsMandatory);
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $allowance->update($data);
        $allowance->grades()->sync($request->input('Grades', []));

        return redirect()->route('hr.statutory.allowances.index')->with('success', 'Allowance updated.');
    }

    public function destroy($id)
    {
        $allowance = PayrollAllowance::findOrFail($id);
        $allowance->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.allowances.index')->with('success', 'Allowance deactivated.');
    }

    public function activate($id)
    {
        $allowance = PayrollAllowance::findOrFail($id);
        $allowance->update([
            'IsActive' => 1,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.allowances.index')->with('success', 'Allowance activated.');
    }
}
