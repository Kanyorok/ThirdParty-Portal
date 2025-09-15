<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\ControlType;
use Illuminate\Http\Request;

class ControlTypeController extends Controller
{
    public function index()
    {
        $controls = ControlType::orderBy('Name')->get();
        return view('legal.setup.control_types.index', compact('controls'));
    }

    public function create()
    {
        return view('legal.setup.control_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:100',
            'Description' => 'nullable|string|max:255',
        ]);

        ControlType::create($validated + ['IsActive' => 1]);
        return redirect()->route('legal.setup.control_types.index')
            ->with('success', 'Control Type added successfully.');
    }

    public function edit($id)
    {
        $control = ControlType::findOrFail($id);
        return view('legal.setup.control_types.edit', compact('control'));
    }

    public function update(Request $request, $id)
    {
        $control = ControlType::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:100',
            'Description' => 'nullable|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        $control->update($validated);
        return redirect()->route('legal.setup.control_types.index')
            ->with('success', 'Control Type updated successfully.');
    }

    public function destroy($id)
    {
        $control = ControlType::findOrFail($id);
        $control->delete();
        return back()->with('success', 'Control Type deleted.');
    }
}
