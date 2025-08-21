<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalCaseEvidence;
use App\Models\Legal\LegalCase;
use Illuminate\Support\Facades\Auth;

class LegalCaseEvidenceController extends Controller
{
    public function index($caseId)
    {
        $case = LegalCase::findOrFail($caseId);
        $evidence = LegalCaseEvidence::where('LegalCaseID', $caseId)->get();

        return view('legal.disputes.evidence.index', compact('case', 'evidence'));
    }

    public function create($caseId)
    {
        $case = LegalCase::findOrFail($caseId);
        return view('legal.disputes.evidence.create', compact('case'));
    }

    public function store(Request $request, $caseId)
    {
        $validated = $request->validate([
            'EvidenceTitle' => 'required|string',
            'Description' => 'nullable|string',
            'DMSDocumentID' => 'nullable|string',
            'ExternalLink' => 'nullable|url'
        ]);

        $evidence = LegalCaseEvidence::create([
            'LegalCaseID' => $caseId,
            'EvidenceTitle' => $validated['EvidenceTitle'],
            'Description' => $validated['Description'],
            'DMSDocumentID' => $validated['DMSDocumentID']?? null,
            'ExternalLink' => $validated['ExternalLink']?? null,
            'IsActive' => $validated['IsActive'] ?? 'Active', // Default to inactive
            'UploadedBy' => Auth::id(),
            'UploadedOn' => now(),
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.evidence.index', $caseId)
            ->with('success', 'Evidence linked successfully.');
    }

    public function show($id)
    {
        $evidence = LegalCaseEvidence::with('case:Id,CaseTitle,CaseNumber')->findOrFail($id);

        return view('legal.disputes.evidence.show', compact('evidence'));
    }

    public function edit($case, $id)
    {
        $cases = LegalCase::findOrFail($case);
        $evidence = LegalCaseEvidence::where('LegalCaseID', $case)->findOrFail($id);

        return view('legal.disputes.evidence.edit', compact('cases', 'evidence'));
    }

    public function update(Request $request, $caseId, $id)
    {
        $evidence = LegalCaseEvidence::findOrFail($id);

        $validated = $request->validate([
            'EvidenceTitle' => 'required|string',
            'Description' => 'nullable|string',
            'DMSDocumentID' => 'nullable|string',
            'ExternalLink' => 'nullable|url',
            'IsActive' => 'string'
        ]);

        $evidence->update([
            'EvidenceTitle' => $validated['EvidenceTitle'],
            'Description' => $validated['Description'],
            'DMSDocumentID' => $validated['DMSDocumentID'] ?? null,
            'ExternalLink' => $validated['ExternalLink'] ?? null,
            'IsActive' => $request->has('IsActive') ? 'Active' : 'Inactive',
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.evidence.index', $caseId)
            ->with('success', 'Evidence updated successfully.');
    }
}

