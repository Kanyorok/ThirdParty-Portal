<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalIntellectualProperty;
use Illuminate\Support\Facades\Auth;

class LegalIntellectualPropertyController extends Controller
{
    public function index()
    {
        // $records = LegalIntellectualProperty::where('IsActive', 1)->orderByDesc('CreatedOn')->get();
        return view('legal.intellectual.index');
    }

    public function create()
    {
        return view('legal.intellectual.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'IPType' => 'required|string',
            'Title' => 'required|string',
            'Owner' => 'nullable|string',
            'RegistrationNumber' => 'nullable|string',
            'RegistrationDate' => 'nullable|date',
            'ExpiryDate' => 'nullable|date',
            'Status' => 'required|string',
            'Remarks' => 'nullable|string',
        ]);

        $validated['CreatedBy'] = Auth::id();
        $validated['CreatedOn'] = now();

        LegalIntellectualProperty::create($validated);

        return redirect()->route('legal.intellectual.index')->with('success', 'Intellectual Property registered successfully.');
    }

    public function show($id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);
        return view('legal.intellectual.show', compact('record'));
    }

    public function edit($id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);
        return view('legal.intellectual.edit', compact('record'));
    }

    public function update(Request $request, $id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);

        $validated = $request->validate([
            'IPType' => 'required|string',
            'Title' => 'required|string',
            'Owner' => 'nullable|string',
            'RegistrationNumber' => 'nullable|string',
            'RegistrationDate' => 'nullable|date',
            'ExpiryDate' => 'nullable|date',
            'Status' => 'required|string',
            'Remarks' => 'nullable|string',
        ]);

        $validated['ModifiedBy'] = Auth::id();
        $validated['ModifiedOn'] = now();

        $record->update($validated);

        return redirect()->route('legal.intellectual.index')->with('success', 'Intellectual Property record updated.');
    }

    public function destroy($id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);
        $record->update([
            'DeletedBy' => Auth::id(),
            'DeletedOn' => now(),
            'IsActive' => 0,
        ]);

        return back()->with('success', 'Record archived successfully.');
    }
}
