<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
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
        return view('hr.statutory.allowances.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRPayrollAllowances,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsTaxable' => ['nullable', 'boolean'],
        ]);

        $data['IsTaxable'] = $request->boolean('IsTaxable', true);
        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        PayrollAllowance::create($data);

        return redirect()->route('hr.statutory.allowances.index')->with('success', 'Allowance created.');
    }

    public function edit($id)
    {
        $allowance = PayrollAllowance::findOrFail($id);
        return view('hr.statutory.allowances.edit', compact('allowance'));
    }

    public function update(Request $request, $id)
    {
        $allowance = PayrollAllowance::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRPayrollAllowances', 'Code')->ignore($allowance->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsTaxable' => ['nullable', 'boolean'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsTaxable'] = $request->boolean('IsTaxable', $allowance->IsTaxable);
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $allowance->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $allowance->update($data);

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
}
