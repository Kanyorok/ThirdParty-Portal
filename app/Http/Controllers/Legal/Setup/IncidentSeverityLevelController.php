<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\IncidentSeverityLevel;
use Illuminate\Http\Request;

class IncidentSeverityLevelController extends Controller
{
    public function index()
    {
        $levels = IncidentSeverityLevel::orderBy('Id')->get();
        return view('legal.setup.incident_severity_levels.index', compact('levels'));
    }

    public function create()
    {
        return view('legal.setup.incident_severity_levels.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:100',
            'Description' => 'nullable|string|max:255',
        ]);

        IncidentSeverityLevel::create($validated + ['IsActive' => 1]);
        return redirect()->route('legal.setup.incident_severity_levels.index')
            ->with('success', 'Incident Severity Level added successfully.');
    }

    public function edit($id)
    {
        $level = IncidentSeverityLevel::findOrFail($id);
        return view('legal.setup.incident_severity_levels.edit', compact('level'));
    }

    public function update(Request $request, $id)
    {
        $level = IncidentSeverityLevel::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:100',
            'Description' => 'nullable|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        $level->update($validated);
        return redirect()->route('legal.setup.incident_severity_levels.index')
            ->with('success', 'Incident Severity Level updated successfully.');
    }

    public function destroy($id)
    {
        $level = IncidentSeverityLevel::findOrFail($id);
        $level->delete();
        return back()->with('success', 'Incident Severity Level deleted.');
    }
}
