<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\FilingType;
use Illuminate\Http\Request;

class FilingTypeController extends Controller
{
    public function index()
    {
        $filings = FilingType::orderBy('Name')->get();
        return view('legal.setup.filing_types.index', compact('filings'));
    }

    public function create()
    {
        return view('legal.setup.filing_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
        ]);

        FilingType::create($validated + ['IsActive' => 1]);
        return redirect()->route('legal.setup.filing_types.index')
            ->with('success', 'Filing Type added successfully.');
    }

    public function edit($id)
    {
        $filing = FilingType::findOrFail($id);
        return view('legal.setup.filing_types.edit', compact('filing'));
    }

    public function update(Request $request, $id)
    {
        $filing = FilingType::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Description' => 'nullable|string|max:255',
            'IsActive' => 'nullable|boolean',
        ]);

        $filing->update($validated);
        return redirect()->route('legal.setup.filing_types.index')
            ->with('success', 'Filing Type updated successfully.');
    }

    public function destroy($id)
    {
        $filing = FilingType::findOrFail($id);
        $filing->delete();
        return back()->with('success', 'Filing Type deleted.');
    }
}
