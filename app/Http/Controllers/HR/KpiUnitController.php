<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiUnitController extends Controller
{
    public function index()
    {
        $units = KpiUnit::orderBy('Name')->paginate(20);
        return view('hr.config.kpi.units.index', compact('units'));
    }

    public function create()
    {
        return view('hr.config.kpi.units.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRKPIUnits,Code'],
            'Name' => ['required', 'string', 'max:100'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiUnit::create($data);

        return redirect()->route('hr.config.kpi.units.index')->with('success', 'Unit created.');
    }

    public function edit($id)
    {
        $unit = KpiUnit::findOrFail($id);
        return view('hr.config.kpi.units.edit', compact('unit'));
    }

    public function update(Request $request, $id)
    {
        $unit = KpiUnit::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRKPIUnits', 'Code')->ignore($unit->Id, 'Id')],
            'Name' => ['required', 'string', 'max:100'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $unit->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $unit->update($data);

        return redirect()->route('hr.config.kpi.units.index')->with('success', 'Unit updated.');
    }

    public function destroy($id)
    {
        $unit = KpiUnit::findOrFail($id);
        $unit->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.units.index')->with('success', 'Unit deactivated.');
    }
}
