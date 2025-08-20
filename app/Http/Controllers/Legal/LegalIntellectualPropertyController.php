<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;
use App\Models\Legal\LegalIntellectualProperty;
use Illuminate\Support\Facades\Auth;

class LegalIntellectualPropertyController extends Controller
{
    public function index()
    {
        $records = LegalIntellectualProperty::all();
        return view('legal.intellectual.index', compact('records'));
    }

    public function create()
    {
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'IPTypes')
            ->get();
        return view('legal.intellectual.create', compact('details'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'IPType' => 'required|exists:t_CodeDetails,Value',
            'Title' => 'required|string',
            'Owner' => 'required|string',
            'RegistrationNumber' => 'required|string',
            'RegistrationDate' => 'required|date',
            'ExpiryDate' => 'required|date',
            'Remarks' => 'required|string',
            'IsDisputed' => 'boolean',
            'DisputeReason' => 'nullable|string',
        ]);

        LegalIntellectualProperty::create([
            'IPType' => $validated['IPType'],
            'Title' => $validated['Title'],
            'Owner' => $validated['Owner'],
            'RegistrationNumber' => $validated['RegistrationNumber'],
            'RegistrationDate' => $validated['RegistrationDate'],
            'ExpiryDate' => $validated['ExpiryDate'],
            'Status' => $validated['Status'] ?? 'Inactive',
            'Remarks' => $validated['Remarks'],
            'IsDisputed' => $validated['IsDisputed'] ?? false,
            'DisputeReason' => $validated['DisputeReason'] ?? null,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::id(),
        ]);

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
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'IPTypes')
            ->get();
        return view('legal.intellectual.edit', compact('record', 'details'));
    }

    public function update(Request $request, $id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);

        $validated = $request->validate([
            'IPType' => 'required|string',
            'Title' => 'required|string',
            'Owner' => 'required|string',
            'RegistrationNumber' => 'required|string',
            'RegistrationDate' => 'required|date',
            'ExpiryDate' => 'required|date',
            'Status' => 'required|string',
            'Remarks' => 'nullable|string',
            'IsDisputed' => 'boolean',
            'DisputeReason' => 'nullable|string',
        ]);

        $validated['ModifiedBy'] = Auth::id();
        $validated['ModifiedOn'] = now();

        $record->update($validated);

        return redirect()->route('legal.intellectual.index')->with('success', 'Intellectual Property record updated.');
    }

    public function raiseDispute(Request $request, $id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);
        $validated = $request->validate([
            'IsDisputed' => 'required|boolean',
            'DisputeReason' => 'required|string',
        ]);

        $record->update([
            'IsDisputed' => $validated['IsDisputed'],
            'DisputeReason' => $validated['DisputeReason'],
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.intellectual.index')->with('success', 'Dispute raised successfully.');
    }

    public function destroy($id)
    {
        $record = LegalIntellectualProperty::findOrFail($id);
        $record->DeletedBy = Auth::id();
        $record->save();
        $record->delete();

        return back()->with('success', 'Record archived successfully.');
    }
}
