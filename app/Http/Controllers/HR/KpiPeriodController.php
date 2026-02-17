<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiPeriod;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiPeriodController extends Controller
{
    public function index()
    {
        $periods = KpiPeriod::orderBy('Name')->paginate(20);

        return view('hr.config.kpi.periods.index', compact('periods'));
    }

    public function create()
    {
        return view('hr.config.kpi.periods.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRKPIPeriods,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'StartMonth' => ['required', 'integer', 'between:1,12'],
            'EndMonth' => ['required', 'integer', 'between:1,12', 'gte:StartMonth'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiPeriod::create($data);

        return redirect()->route('hr.config.kpi.periods.index')->with('success', 'KPI period created.');
    }

    public function edit($id)
    {
        $period = KpiPeriod::findOrFail($id);

        return view('hr.config.kpi.periods.edit', compact('period'));
    }

    public function update(Request $request, $id)
    {
        $period = KpiPeriod::findOrFail($id);

        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRKPIPeriods', 'Code')->ignore($period->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'StartMonth' => ['required', 'integer', 'between:1,12'],
            'EndMonth' => ['required', 'integer', 'between:1,12', 'gte:StartMonth'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $period->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $period->update($data);

        return redirect()->route('hr.config.kpi.periods.index')->with('success', 'KPI period updated.');
    }

    public function destroy($id)
    {
        $period = KpiPeriod::findOrFail($id);
        $period->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.periods.index')->with('success', 'KPI period deactivated.');
    }
}
