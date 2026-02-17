<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\StatutoryNhifRate;
use Illuminate\Http\Request;

class StatutoryNhifController extends Controller
{
    public function index()
    {
        $rates = StatutoryNhifRate::orderBy('EffectiveFrom')->paginate(20);
        return view('hr.statutory.nhif.index', compact('rates'));
    }

    public function create()
    {
        return view('hr.statutory.nhif.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeRate'  => ['required', 'numeric', 'min:0'],
            'MinAmount'     => ['required', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
        ]);

        $data['BandName'] = 'SHA/SHIF';
        $data['IncomeFrom'] = 0;
        $data['IncomeTo'] = null;
        $data['EmployerRate'] = 0;
        $data['IsPercentage'] = true;
        $data['IsActive'] = 1;
        $data['CreatedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['CreatedOn'] = now();

        StatutoryNhifRate::create($data);

        return redirect()->route('hr.statutory.nhif.index')->with('success', 'NHIF/SHIF rate created.');
    }

    public function edit($id)
    {
        $rate = StatutoryNhifRate::findOrFail($id);
        return view('hr.statutory.nhif.edit', compact('rate'));
    }

    public function update(Request $request, $id)
    {
        $rate = StatutoryNhifRate::findOrFail($id);
        $data = $request->validate([
            'EmployeeRate'  => ['required', 'numeric', 'min:0'],
            'MinAmount'     => ['required', 'numeric', 'min:0'],
            'EffectiveFrom' => ['required', 'date'],
            'EffectiveTo'   => ['nullable', 'date', 'after:EffectiveFrom'],
            'Description'   => ['nullable', 'string', 'max:255'],
            'IsActive'      => ['nullable', 'boolean'],
        ]);

        $data['BandName'] = 'SHA/SHIF';
        $data['IncomeFrom'] = 0;
        $data['IncomeTo'] = null;
        $data['EmployerRate'] = 0;
        $data['IsPercentage'] = true;
        $data['IsActive'] = $request->has('IsActive') ? $request->boolean('IsActive') : $rate->IsActive;
        $data['ModifiedBy'] = $request->user()->Id ?? $request->user()->id ?? null;
        $data['ModifiedOn'] = now();

        $rate->update($data);

        return redirect()->route('hr.statutory.nhif.index')->with('success', 'NHIF/SHIF rate updated.');
    }

    public function destroy($id)
    {
        $rate = StatutoryNhifRate::findOrFail($id);
        $rate->update([
            'IsActive'  => 0,
            'DeletedBy' => auth()->id(),
            'DeletedOn' => now(),
        ]);

        return redirect()->route('hr.statutory.nhif.index')->with('success', 'NHIF/SHIF rate deactivated.');
    }
}
