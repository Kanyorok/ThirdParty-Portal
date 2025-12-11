<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollDeduction;
use App\Models\HR\PayrollDeductionRule;
use Illuminate\Http\Request;

class PayrollDeductionRuleController extends Controller
{
    public function index($deductionId)
    {
        $deduction = PayrollDeduction::findOrFail($deductionId);
        $rules = $deduction->rules()->orderBy('IncomeFrom')->paginate(20);
        return view('hr.statutory.deductions.rules.index', compact('deduction', 'rules'));
    }

    public function create($deductionId)
    {
        $deduction = PayrollDeduction::findOrFail($deductionId);
        return view('hr.statutory.deductions.rules.create', compact('deduction'));
    }

    public function store(Request $request, $deductionId)
    {
        $deduction = PayrollDeduction::findOrFail($deductionId);
        $data = $this->validateRule($request);
        $data['DeductionID'] = $deduction->Id;
        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        PayrollDeductionRule::create($data);

        return redirect()->route('hr.statutory.deductions.rules.index', $deduction->Id)->with('success', 'Rule created.');
    }

    public function edit($deductionId, $id)
    {
        $deduction = PayrollDeduction::findOrFail($deductionId);
        $rule = PayrollDeductionRule::where('DeductionID', $deductionId)->findOrFail($id);
        return view('hr.statutory.deductions.rules.edit', compact('deduction', 'rule'));
    }

    public function update(Request $request, $deductionId, $id)
    {
        $deduction = PayrollDeduction::findOrFail($deductionId);
        $rule = PayrollDeductionRule::where('DeductionID', $deductionId)->findOrFail($id);
        $data = $this->validateRule($request);
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $rule->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $rule->update($data);

        return redirect()->route('hr.statutory.deductions.rules.index', $deduction->Id)->with('success', 'Rule updated.');
    }

    public function destroy($deductionId, $id)
    {
        $deduction = PayrollDeduction::findOrFail($deductionId);
        $rule = PayrollDeductionRule::where('DeductionID', $deductionId)->findOrFail($id);
        $rule->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.deductions.rules.index', $deduction->Id)->with('success', 'Rule deactivated.');
    }

    protected function validateRule(Request $request): array
    {
        return $request->validate([
            'CalcMethod'   => ['required', 'string', 'max:50'],
            'Rate'         => ['nullable', 'numeric', 'min:0'],
            'Amount'       => ['nullable', 'numeric', 'min:0'],
            'IncomeFrom'   => ['nullable', 'numeric', 'min:0'],
            'IncomeTo'     => ['nullable', 'numeric', 'gte:IncomeFrom'],
            'MinAmount'    => ['nullable', 'numeric', 'min:0'],
            'MaxAmount'    => ['nullable', 'numeric', 'min:0'],
            'HasRelief'    => ['nullable', 'boolean'],
            'ReliefType'   => ['nullable', 'string', 'max:20'],
            'ReliefRate'   => ['nullable', 'numeric', 'min:0'],
            'ReliefAmount' => ['nullable', 'numeric', 'min:0'],
            'EffectiveFrom'=> ['required', 'date'],
            'EffectiveTo'  => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'  => ['nullable', 'string', 'max:255'],
            'FormulaText'  => ['nullable', 'string'],
        ]);
    }
}
