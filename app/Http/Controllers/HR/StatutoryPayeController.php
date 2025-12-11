<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\StatutoryPayeBand;
use Illuminate\Http\Request;

class StatutoryPayeController extends Controller
{
    public function index()
    {
        $bands = StatutoryPayeBand::orderBy('LowerLimit')->paginate(20);
        return view('hr.statutory.paye.index', compact('bands'));
    }

    public function create()
    {
        return view('hr.statutory.paye.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'LowerLimit'    => ['required', 'numeric', 'min:0'],
            'UpperLimit'    => ['nullable', 'numeric', 'gte:LowerLimit'],
            'Rate'          => ['required', 'numeric', 'min:0'],
            'FixedAmount'   => ['nullable', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        StatutoryPayeBand::create($data);

        return redirect()->route('hr.statutory.paye.index')->with('success', 'PAYE band created.');
    }

    public function edit($id)
    {
        $band = StatutoryPayeBand::findOrFail($id);
        return view('hr.statutory.paye.edit', compact('band'));
    }

    public function update(Request $request, $id)
    {
        $band = StatutoryPayeBand::findOrFail($id);
        $data = $request->validate([
            'LowerLimit'    => ['required', 'numeric', 'min:0'],
            'UpperLimit'    => ['nullable', 'numeric', 'gte:LowerLimit'],
            'Rate'          => ['required', 'numeric', 'min:0'],
            'FixedAmount'   => ['nullable', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
            'IsActive'      => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $band->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $band->update($data);

        return redirect()->route('hr.statutory.paye.index')->with('success', 'PAYE band updated.');
    }

    public function destroy($id)
    {
        $band = StatutoryPayeBand::findOrFail($id);
        $band->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.paye.index')->with('success', 'PAYE band deactivated.');
    }
}
