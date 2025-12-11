<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\StatutoryRelief;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StatutoryReliefController extends Controller
{
    public function index()
    {
        $reliefs = StatutoryRelief::orderBy('Name')->paginate(20);
        return view('hr.statutory.reliefs.index', compact('reliefs'));
    }

    public function create()
    {
        return view('hr.statutory.reliefs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code'          => ['required', 'string', 'max:50', 'unique:t_HRStatutoryReliefs,Code'],
            'Name'          => ['required', 'string', 'max:150'],
            'Amount'        => ['required', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        StatutoryRelief::create($data);

        return redirect()->route('hr.statutory.reliefs.index')->with('success', 'Relief created.');
    }

    public function edit($id)
    {
        $relief = StatutoryRelief::findOrFail($id);
        return view('hr.statutory.reliefs.edit', compact('relief'));
    }

    public function update(Request $request, $id)
    {
        $relief = StatutoryRelief::findOrFail($id);
        $data = $request->validate([
            'Code'          => ['required', 'string', 'max:50', Rule::unique('t_HRStatutoryReliefs', 'Code')->ignore($relief->Id, 'Id')],
            'Name'          => ['required', 'string', 'max:150'],
            'Amount'        => ['required', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
            'IsActive'      => ['nullable', 'boolean'],
        ]);

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
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.reliefs.index')->with('success', 'Relief deactivated.');
    }
}
