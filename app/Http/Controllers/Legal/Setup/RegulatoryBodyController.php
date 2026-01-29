<?php

namespace App\Http\Controllers\Legal\Setup;

use App\Http\Controllers\Controller;
use App\Models\Legal\RegulatoryBody;
use Illuminate\Http\Request;

class RegulatoryBodyController extends Controller
{
    public function index()
    {
        $bodies = RegulatoryBody::orderBy('Name')->get();

        return view('legal.setup.regulatory_bodies.index', compact('bodies'));
    }

    public function create()
    {
        return view('legal.setup.regulatory_bodies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Jurisdiction' => 'nullable|string|max:150',
            'ContactPerson' => 'nullable|string|max:150',
            'ContactEmail' => 'nullable|email|max:150',
            'ContactPhone' => 'nullable|string|max:50',
        ]);

        RegulatoryBody::create($validated + ['IsActive' => 1]);

        return redirect()->route('legal.setup.regulatory_bodies.index')
            ->with('success', 'Regulatory Body added successfully.');
    }

    public function edit($id)
    {
        $body = RegulatoryBody::findOrFail($id);

        return view('legal.setup.regulatory_bodies.edit', compact('body'));
    }

    public function update(Request $request, $id)
    {
        $body = RegulatoryBody::findOrFail($id);
        $validated = $request->validate([
            'Name' => 'required|string|max:150',
            'Jurisdiction' => 'nullable|string|max:150',
            'ContactPerson' => 'nullable|string|max:150',
            'ContactEmail' => 'nullable|email|max:150',
            'ContactPhone' => 'nullable|string|max:50',
            'IsActive' => 'nullable|boolean',
        ]);

        $body->update($validated);

        return redirect()->route('legal.setup.regulatory_bodies.index')
            ->with('success', 'Regulatory Body updated successfully.');
    }

    public function destroy($id)
    {
        $body = RegulatoryBody::findOrFail($id);
        $body->delete();

        return back()->with('success', 'Regulatory Body deleted.');
    }
}
