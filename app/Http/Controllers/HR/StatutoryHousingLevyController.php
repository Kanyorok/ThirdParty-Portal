<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\StatutoryHousingLevyRate;
use Illuminate\Http\Request;

class StatutoryHousingLevyController extends Controller
{
    public function index()
    {
        $rates = StatutoryHousingLevyRate::orderByDesc('EffectiveFrom')->paginate(20);
        return view('hr.statutory.housinglevy.index', compact('rates'));
    }

    public function create()
    {
        return view('hr.statutory.housinglevy.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Rate'          => ['required', 'numeric', 'min:0'],
            'CapAmount'     => ['nullable', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        StatutoryHousingLevyRate::create($data);

        return redirect()->route('hr.statutory.housinglevy.index')->with('success', 'Housing levy rate created.');
    }

    public function edit($id)
    {
        $rate = StatutoryHousingLevyRate::findOrFail($id);
        return view('hr.statutory.housinglevy.edit', compact('rate'));
    }

    public function update(Request $request, $id)
    {
        $rate = StatutoryHousingLevyRate::findOrFail($id);
        $data = $request->validate([
            'Rate'          => ['required', 'numeric', 'min:0'],
            'CapAmount'     => ['nullable', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
            'IsActive'      => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $rate->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $rate->update($data);

        return redirect()->route('hr.statutory.housinglevy.index')->with('success', 'Housing levy rate updated.');
    }

    public function destroy($id)
    {
        $rate = StatutoryHousingLevyRate::findOrFail($id);
        $rate->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.housinglevy.index')->with('success', 'Housing levy rate deactivated.');
    }
}
