<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\StatutoryNssfRate;
use Illuminate\Http\Request;

class StatutoryNssfController extends Controller
{
    public function index()
    {
        $rates = StatutoryNssfRate::orderBy('IncomeFrom')->paginate(20);

        return view('hr.statutory.nssf.index', compact('rates'));
    }

    public function create()
    {
        return view('hr.statutory.nssf.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Tier' => ['nullable', 'string', 'max:50'],
            'IncomeFrom' => ['required', 'numeric', 'min:0'],
            'IncomeTo' => ['nullable', 'numeric', 'gte:IncomeFrom'],
            'EmployeeRate' => ['required', 'numeric', 'min:0'],
            'EmployerRate' => ['required', 'numeric', 'min:0'],
            'IsPercentage' => ['nullable', 'boolean'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['IsPercentage'] = $request->boolean('IsPercentage', true);
        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        StatutoryNssfRate::create($data);

        return redirect()->route('hr.statutory.nssf.index')->with('success', 'NSSF rate created.');
    }

    public function edit($id)
    {
        $rate = StatutoryNssfRate::findOrFail($id);

        return view('hr.statutory.nssf.edit', compact('rate'));
    }

    public function update(Request $request, $id)
    {
        $rate = StatutoryNssfRate::findOrFail($id);
        $data = $request->validate([
            'Tier' => ['nullable', 'string', 'max:50'],
            'IncomeFrom' => ['required', 'numeric', 'min:0'],
            'IncomeTo' => ['nullable', 'numeric', 'gte:IncomeFrom'],
            'EmployeeRate' => ['required', 'numeric', 'min:0'],
            'EmployerRate' => ['required', 'numeric', 'min:0'],
            'IsPercentage' => ['nullable', 'boolean'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo' => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description' => ['nullable', 'string', 'max:255'],
            'IsActive' => ['nullable', 'boolean'],
        ]);

        $data['IsPercentage'] = $request->boolean('IsPercentage', true);
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $rate->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $rate->update($data);

        return redirect()->route('hr.statutory.nssf.index')->with('success', 'NSSF rate updated.');
    }

    public function destroy($id)
    {
        $rate = StatutoryNssfRate::findOrFail($id);
        $rate->update([
            'IsActive' => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.nssf.index')->with('success', 'NSSF rate deactivated.');
    }
}
