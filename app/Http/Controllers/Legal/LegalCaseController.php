<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCaseController extends Controller
{
    public function index()
    {
        $cases = LegalCase::all();
        return view('legal.disputes.index', compact('cases'));
    }

    public function create()
    {
        return view('legal.disputes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'CaseTitle'=> 'required|string',
            'CaseNumber'=> 'required|string',
            'CourtName'=> 'required|string',
            'FilingDate'=> 'required|date',
            'OpposingParty'=> 'required|string',
            'CaseType'=> 'required|string',
            'Summary'=> 'required|string',
            'AssignedCounselID'=> 'nullable',
            'CaseDMSDocID'=> 'nullable',

        ]);

        LegalCase::create([
            'CaseTitle' => $validated['CaseTitle'],
            'CaseNumber' => $validated['CaseNumber'],
            'CourtName' => $validated['CourtName'],
            'FilingDate' => $validated['FilingDate'],
            'OpposingParty' => $validated['OpposingParty'],
            'CaseType' => $validated['CaseType'],
            'Summary' => $validated['Summary'],
            'AssignedCounselID' => $validated['AssignedCounselID']??null,
            'CaseDMSDocID' => $validated['CaseDMSDocID'] ?? null,
            'CreatedBy' => Auth::Id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        return redirect()->route('legal.cases.index')->with('success', 'Legal case created successfully.');
    }

    public function edit($id)
    {
        $case = LegalCase::findOrFail($id);
        return view('legal.disputes.edit', compact('case'));
    }

    public function update(Request $request, $id)
    {
        $case = LegalCase::findOrFail($id);
        $case->update([
            'CaseTitle' => $request->CaseTitle,
            'CaseNumber' => $request->CaseNumber,
            'CourtName' => $request->CourtName,
            'FilingDate' => $request->FilingDate,
            'OpposingParty' => $request->OpposingParty,
            'CaseType' => $request->CaseType,
            'Status' => $request->Status,
            'Summary' => $request->Summary,
            'AssignedCounselID' => $request->AssignedCounselID,
            'CaseDMSDocID' => $request->CaseDMSDocID,
            'ModifiedBy' => Auth::Id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.index')->with('success', 'Legal case updated successfully.');
    }

    public function show($id)
    {
        $case = LegalCase::findOrFail($id);
        $outcomes = LegalCaseOutcome::where('LegalCaseID', $id)->get();
        return view('legal.disputes.show', compact('case', 'outcomes'));
    }

    public function destroy($id)
    {
        $case = LegalCase::findOrFail($id);
        
        $case->delete();

        return redirect()->route('legal.cases.index')->with('success', 'Legal case deleted successfully.');
    }

}
