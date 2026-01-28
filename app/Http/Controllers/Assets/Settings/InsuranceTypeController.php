<?php

namespace App\Http\Controllers\Assets\Settings;

use App\Http\Controllers\Controller;
use App\Models\Assets\Settings\InsuranceType;
use Illuminate\Http\Request;

class InsuranceTypeController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->get('q');
        $rows = InsuranceType::when($q, fn ($qq) =>
                    $qq->where('Code', 'like', "%$q%")
                       ->orWhere('Name', 'like', "%$q%"))
                ->orderBy('Name')->paginate(20);

        return view('assets.settings.insurancetypes.index', compact('rows', 'q'));
    }

    public function create()
    {
        return view('assets.settings.insurancetypes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_InsuranceTypes,Code',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        InsuranceType::create($data);

        return redirect()->route('assets.settings.insurance-types.index')
            ->with('success', 'Insurance type created.');
    }

    public function edit(int $id)
    {
        $row = InsuranceType::findOrFail($id);

        return view('assets.settings.insurancetypes.edit', compact('row'));
    }

    public function update(Request $request, int $id)
    {
        $row = InsuranceType::findOrFail($id);

        $data = $request->validate([
            'Code' => 'required|max:30|unique:t_InsuranceTypes,Code,' . $row->Id . ',Id',
            'Name' => 'required|max:100',
            'IsActive' => 'nullable|boolean',
        ]);

        $data['IsActive'] = $request->boolean('IsActive');
        $row->update($data);

        return redirect()->route('assets.settings.insurance-types.index')
            ->with('success', 'Insurance type updated.');
    }

    public function destroy(int $id)
    {
        InsuranceType::where('Id', $id)->delete();

        return redirect()->route('assets.settings.insurance-types.index')
            ->with('success', 'Insurance type deleted.');
    }

    public function show(int $id)
    {
        $row = \App\Models\Assets\Settings\InsuranceType::findOrFail($id);

        return view('assets.settings.insurancetypes.show', compact('row'));
    }
}
