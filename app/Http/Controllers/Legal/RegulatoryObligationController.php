<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\RegulatoryObligation;
use Illuminate\Http\Request;

class RegulatoryObligationController extends Controller
{
    public function index()
    {
        $obligations = RegulatoryObligation::latest()->get();

        return view('legal.compliance.obligations.index', compact('obligations'));
    }

    public function create()
    {
        return view('legal.compliance.obligations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ObligationTitle' => 'required|string|max:255',
            'ObligationDescription' => 'nullable|string',
            'RegulatoryBody' => 'required|string|max:150',
            'ObligationType' => 'required|string|max:100',
            'EffectiveDate' => 'required|date',
            'DueDate' => 'nullable|date',
            'IsRecurring' => 'required|boolean',
            'RecurrenceType' => 'nullable|string|max:50',
            'Status' => 'required|string|max:50',
            'ComplianceArea' => 'nullable|string|max:100',
            'AttachmentPath' => 'nullable|string|max:255',
        ]);

        RegulatoryObligation::create($validated);

        return redirect()->route('legal.compliance.obligations.index')->with('success', 'Obligation created successfully.');
    }

    public function edit($id)
    {
        $obligation = RegulatoryObligation::findOrFail($id);

        return view('legal.compliance.obligations.edit', compact('obligation'));
    }

    public function update(Request $request, $id)
    {
        $obligation = RegulatoryObligation::findOrFail($id);

        $validated = $request->validate([
            'ObligationTitle' => 'required|string|max:255',
            'ObligationDescription' => 'nullable|string',
            'RegulatoryBody' => 'required|string|max:150',
            'ObligationType' => 'required|string|max:100',
            'EffectiveDate' => 'required|date',
            'DueDate' => 'nullable|date',
            'IsRecurring' => 'required|boolean',
            'RecurrenceType' => 'nullable|string|max:50',
            'Status' => 'required|string|max:50',
            'ComplianceArea' => 'nullable|string|max:100',
            'AttachmentPath' => 'nullable|string|max:255',
        ]);

        $obligation->update($validated);

        return redirect()->route('legal.compliance.obligations.index')->with('success', 'Obligation updated successfully.');
    }
}
