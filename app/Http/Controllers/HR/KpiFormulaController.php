<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiFormula;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiFormulaController extends Controller
{
    public function index()
    {
        $formulas = KpiFormula::orderBy('Name')->paginate(20);
        return view('hr.config.kpi.formulas.index', compact('formulas'));
    }

    public function create()
    {
        return view('hr.config.kpi.formulas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code'        => ['required', 'string', 'max:50', 'unique:t_HRKPIFormulas,Code'],
            'Name'        => ['required', 'string', 'max:150'],
            'Expression'  => ['required', 'string'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiFormula::create($data);

        return redirect()->route('hr.config.kpi.formulas.index')->with('success', 'Formula created.');
    }

    public function edit($id)
    {
        $formula = KpiFormula::findOrFail($id);
        return view('hr.config.kpi.formulas.edit', compact('formula'));
    }

    public function update(Request $request, $id)
    {
        $formula = KpiFormula::findOrFail($id);
        $data = $request->validate([
            'Code'        => ['required', 'string', 'max:50', Rule::unique('t_HRKPIFormulas', 'Code')->ignore($formula->Id, 'Id')],
            'Name'        => ['required', 'string', 'max:150'],
            'Expression'  => ['required', 'string'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive'    => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $formula->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $formula->update($data);

        return redirect()->route('hr.config.kpi.formulas.index')->with('success', 'Formula updated.');
    }

    public function destroy($id)
    {
        $formula = KpiFormula::findOrFail($id);
        $formula->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.formulas.index')->with('success', 'Formula deactivated.');
    }
}
