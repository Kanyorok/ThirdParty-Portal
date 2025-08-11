<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCaseController extends Controller
{
    public function index()
    {
        // $cases = LegalCase::whereNull('DeletedOn')->orderByDesc('CreatedOn')->get();
        return view('legal.disputes.index');
    }

    public function create()
    {
        return view('legal.disputes.create');
    }

    public function store(Request $request)
    {
        LegalCase::create([
            'CaseTitle' => $request->CaseTitle,
            'CaseNumber' => $request->CaseNumber,
            'CourtName' => $request->CourtName,
            'FilingDate' => $request->FilingDate,
            'OpposingParty' => $request->OpposingParty,
            'CaseType' => $request->CaseType,
            'Status' => $request->Status,
            'Summary' => $request->Summary,
            'AssignedCounselID' => $request->AssignedCounselID,
            'DMSDocID' => $request->DMSDocID,
            'IsActive' => 1,
            'CreatedBy' => Auth::Id(),
            'CreatedOn' => now(),
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
            'DMSDocID' => $request->DMSDocID,
            'ModifiedBy' => Auth::Id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.index')->with('success', 'Legal case updated successfully.');
    }

    public function show($id)
    {
        $case = LegalCase::findOrFail($id);
        return view('legal.disputes.show', compact('case'));
    }
}
