<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollDeduction;
use App\Models\HR\StatutoryRelief;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class StatutoryReliefController extends Controller
{
    public function index()
    {
        $reliefs = StatutoryRelief::orderBy('Name')->paginate(20);
        $deductionLookup = PayrollDeduction::pluck('Name', 'Id');

        return view('hr.statutory.reliefs.index', compact('reliefs', 'deductionLookup'));
    }

    public function create()
    {
        $deductions = PayrollDeduction::where('IsActive', 1)
            ->where('Code', '!=', 'PAYE')
            ->orderBy('Name')
            ->get(['Id', 'Name', 'Code']);

        return view('hr.statutory.reliefs.create', compact('deductions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRStatutoryReliefs,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'ReliefType' => ['required', 'string', 'in:Fixed,Percentage'],
            'ApplyStage' => ['required', 'string', 'in:PreTax,PostTax'],
            'Amount' => ['nullable', 'numeric', 'min:0', 'required_if:ReliefType,Fixed'],
            'ReliefRate' => ['nullable', 'numeric', 'min:0', 'required_if:ReliefType,Percentage'],
            'DeductionID' => ['nullable', 'exists:t_HRPayrollDeductions,Id', 'required_if:ReliefType,Percentage'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $this->normalizeReliefData($data);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        StatutoryRelief::create($data);

        return redirect()->route('hr.statutory.reliefs.index')->with('success', 'Relief created.');
    }

    public function edit($id)
    {
        $relief = StatutoryRelief::findOrFail($id);
        $deductions = PayrollDeduction::where('IsActive', 1)
            ->where('Code', '!=', 'PAYE')
            ->orderBy('Name')
            ->get(['Id', 'Name', 'Code']);

        return view('hr.statutory.reliefs.edit', compact('relief', 'deductions'));
    }

    public function update(Request $request, $id)
    {
        $relief = StatutoryRelief::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRStatutoryReliefs', 'Code')->ignore($relief->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'ReliefType' => ['required', 'string', 'in:Fixed,Percentage'],
            'ApplyStage' => ['required', 'string', 'in:PreTax,PostTax'],
            'Amount' => ['nullable', 'numeric', 'min:0', 'required_if:ReliefType,Fixed'],
            'ReliefRate' => ['nullable', 'numeric', 'min:0', 'required_if:ReliefType,Percentage'],
            'DeductionID' => ['nullable', 'exists:t_HRPayrollDeductions,Id', 'required_if:ReliefType,Percentage'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $this->normalizeReliefData($data);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $relief->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $relief->update($data);

        return redirect()->route('hr.statutory.reliefs.index')->with('success', 'Relief updated.');
    }

    public function destroy($id)
    {
        $relief = StatutoryRelief::findOrFail($id);
        $relief->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.reliefs.index')->with('success', 'Relief deactivated.');
    }

    public function activate($id)
    {
        $relief = StatutoryRelief::findOrFail($id);
        $relief->update([
            'IsActive' => 1,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.reliefs.index')->with('success', 'Relief activated.');
    }

    private function normalizeReliefData(array &$data): void
    {
        if (($data['ReliefType'] ?? '') === 'Percentage') {
            $deduction = PayrollDeduction::find($data['DeductionID']);
            if (! $deduction || strcasecmp($deduction->Code, 'PAYE') === 0) {
                throw ValidationException::withMessages([
                    'DeductionID' => 'Select a valid deduction (PAYE is not allowed for relief calculations).',
                ]);
            }
            $data['Amount'] = 0;
        } else {
            $data['ReliefRate'] = null;
            $data['DeductionID'] = null;
        }
    }
}
