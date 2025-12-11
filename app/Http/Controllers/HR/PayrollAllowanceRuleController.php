<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\PayrollAllowance;
use App\Models\HR\PayrollAllowanceRule;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PayrollAllowanceRuleController extends Controller
{
    private const ALLOWED_METHODS = ['PercentageOnBasic', 'PercentageOnGross', 'Flat', 'PercentageOnBand', 'FlatOnBand'];

    public function index($allowanceId)
    {
        $allowance = PayrollAllowance::findOrFail($allowanceId);
        $rules = $allowance->rules()->orderBy('IncomeFrom')->paginate(20);
        return view('hr.statutory.allowances.rules.index', compact('allowance', 'rules'));
    }

    public function create($allowanceId)
    {
        $allowance = PayrollAllowance::findOrFail($allowanceId);
        return view('hr.statutory.allowances.rules.create', compact('allowance'));
    }

    public function store(Request $request, $allowanceId)
    {
        $allowance = PayrollAllowance::findOrFail($allowanceId);
        $data = $this->validateRule($request);
        $data['AllowanceID'] = $allowance->Id;
        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        PayrollAllowanceRule::create($data);

        return redirect()->route('hr.statutory.allowances.rules.index', $allowance->Id)->with('success', 'Rule created.');
    }

    public function edit($allowanceId, $id)
    {
        $allowance = PayrollAllowance::findOrFail($allowanceId);
        $rule = PayrollAllowanceRule::where('AllowanceID', $allowanceId)->findOrFail($id);
        return view('hr.statutory.allowances.rules.edit', compact('allowance', 'rule'));
    }

    public function update(Request $request, $allowanceId, $id)
    {
        $allowance = PayrollAllowance::findOrFail($allowanceId);
        $rule = PayrollAllowanceRule::where('AllowanceID', $allowanceId)->findOrFail($id);
        $data = $this->validateRule($request);
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $rule->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $rule->update($data);

        return redirect()->route('hr.statutory.allowances.rules.index', $allowance->Id)->with('success', 'Rule updated.');
    }

    public function destroy($allowanceId, $id)
    {
        $rule = PayrollAllowanceRule::where('AllowanceID', $allowanceId)->findOrFail($id);
        $rule->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.allowances.rules.index', $allowanceId)->with('success', 'Rule deactivated.');
    }

    protected function validateRule(Request $request): array
    {
        return $request->validate([
            'CalcMethod'   => ['required', 'string', 'max:50', Rule::in(self::ALLOWED_METHODS)],
            'Rate'         => ['nullable', 'numeric', 'min:0'],
            'Amount'       => ['nullable', 'numeric', 'min:0'],
            'IncomeFrom'   => ['nullable', 'numeric', 'min:0'],
            'IncomeTo'     => ['nullable', 'numeric', 'gte:IncomeFrom'],
            'MinAmount'    => ['nullable', 'numeric', 'min:0'],
            'MaxAmount'    => ['nullable', 'numeric', 'min:0'],
            'FormulaText'  => ['nullable', 'string'],
            'EffectiveFrom'=> ['required', 'date'],
            'EffectiveTo'  => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'  => ['nullable', 'string', 'max:255'],
        ]);
    }
}
