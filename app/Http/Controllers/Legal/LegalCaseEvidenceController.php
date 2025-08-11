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
        $request->validate([
            'EvidenceTitle' => 'required|string',
            'Description' => 'nullable|string',
            'DMSDocumentID' => 'nullable|string',
            'ExternalLink' => 'nullable|url'
        ]);

        LegalCaseEvidence::create([
            'LegalCaseID' => $caseId,
            'EvidenceTitle' => $request->EvidenceTitle,
            'Description' => $request->Description,
            'DMSDocumentID' => $request->DMSDocumentID,
            'ExternalLink' => $request->ExternalLink,
            'UploadedBy' => Auth::id(),
            'UploadedOn' => now(),
            'IsActive' => 1,
        ]);

        return redirect()->route('legal.cases.evidence.index', $caseId)
            ->with('success', 'Evidence linked successfully.');
    }
}

