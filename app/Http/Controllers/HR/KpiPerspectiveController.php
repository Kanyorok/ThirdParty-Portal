<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiPerspective;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KpiPerspectiveController extends Controller
{
    public function index()
    {
        $perspectives = KpiPerspective::orderBy('Name')->paginate(20);

        return view('hr.config.kpi.perspectives.index', compact('perspectives'));
    }

    public function create()
    {
        return view('hr.config.kpi.perspectives.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRKPIPerspectives,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        KpiPerspective::create($data);

        return redirect()->route('hr.config.kpi.perspectives.index')->with('success', 'Perspective created.');
    }

    public function edit($id)
    {
        $perspective = KpiPerspective::findOrFail($id);

        return view('hr.config.kpi.perspectives.edit', compact('perspective'));
    }

    public function update(Request $request, $id)
    {
        $perspective = KpiPerspective::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRKPIPerspectives', 'Code')->ignore($perspective->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $perspective->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $perspective->update($data);

        return redirect()->route('hr.config.kpi.perspectives.index')->with('success', 'Perspective updated.');
    }

    public function destroy($id)
    {
        $perspective = KpiPerspective::findOrFail($id);
        $perspective->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.config.kpi.perspectives.index')->with('success', 'Perspective deactivated.');
    }
}
