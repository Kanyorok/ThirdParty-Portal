<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\StatutoryFringeBenefit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StatutoryFringeBenefitController extends Controller
{
    public function index()
    {
        $benefits = StatutoryFringeBenefit::orderBy('Name')->paginate(20);

        return view('hr.statutory.fringe.index', compact('benefits'));
    }

    public function create()
    {
        return view('hr.statutory.fringe.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRStatutoryFringeBenefits,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'RateType' => ['required', 'string', 'max:50', Rule::in(['Percentage', 'Fixed'])],
            'Rate' => ['required', 'numeric', 'min:0'],
            'CapAmount' => ['nullable', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        StatutoryFringeBenefit::create($data);

        return redirect()->route('hr.statutory.fringe.index')->with('success', 'Fringe benefit rule created.');
    }

    public function edit($id)
    {
        $benefit = StatutoryFringeBenefit::findOrFail($id);

        return view('hr.statutory.fringe.edit', compact('benefit'));
    }

    public function update(Request $request, $id)
    {
        $benefit = StatutoryFringeBenefit::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:50', Rule::unique('t_HRStatutoryFringeBenefits', 'Code')->ignore($benefit->Id, 'Id')],
            'Name' => ['required', 'string', 'max:150'],
            'RateType' => ['required', 'string', 'max:50', Rule::in(['Percentage', 'Fixed'])],
            'Rate' => ['required', 'numeric', 'min:0'],
            'CapAmount' => ['nullable', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $benefit->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $benefit->update($data);

        return redirect()->route('hr.statutory.fringe.index')->with('success', 'Fringe benefit rule updated.');
    }

    public function destroy($id)
    {
        $benefit = StatutoryFringeBenefit::findOrFail($id);
        $benefit->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.fringe.index')->with('success', 'Fringe benefit rule deactivated.');
    }
}
