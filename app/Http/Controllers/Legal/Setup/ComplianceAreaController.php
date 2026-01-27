<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\ComplianceArea;
use Illuminate\Http\Request;

class ComplianceAreaController extends Controller
{
    public function index()
    {
        $areas = ComplianceArea::orderBy('Name')->get();

        return view('legal.setup.compliance_areas.index', compact('areas'));
    }

    public function create()
    {
        return view('legal.setup.compliance_areas.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
        ]);

        ComplianceArea::create($validated + ['IsActive' => 1]);

        return redirect()->route('legal.setup.compliance_areas.index')
            ->with('success', 'Compliance Area added successfully.');
    }

    public function edit($id)
    {
        $area = ComplianceArea::findOrFail($id);

        return view('legal.setup.compliance_areas.edit', compact('area'));
    }

    public function update(Request $request, $id)
    {
        $area = ComplianceArea::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        $area->update($validated);

        return redirect()->route('legal.setup.compliance_areas.index')
            ->with('success', 'Compliance Area updated successfully.');
    }

    public function destroy($id)
    {
        $area = ComplianceArea::findOrFail($id);
        $area->delete();

        return back()->with('success', 'Compliance Area deleted.');
    }
}
