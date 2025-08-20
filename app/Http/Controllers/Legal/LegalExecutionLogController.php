<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalExecutionLog;
use App\Models\Legal\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LegalExecutionLogController extends Controller
{
    public function index($documentId)
    {
        $document = LegalDocument::findOrFail($documentId);
        $executionLogs = LegalExecutionLog::where('LegalDocumentID', $documentId)->get();

        return view('legal.execution_logs.index', compact('document', 'executionLogs'));
    }

    public function create($documentId)
    {
        $document = LegalDocument::findOrFail($documentId);
        return view('legal.execution_logs.create', compact('document'));
    }

    public function store(Request $request, $documentId)
    {
        $request->validate([
            'SignedBy' => 'required|string',
            'SignedOn' => 'required|date',
            'Remarks' => 'nullable|string',
        ]);

        LegalExecutionLog::create([
            'LegalDocumentID' => $documentId,
            'SignedBy' => $request->SignedBy,
            'SignedOn' => Carbon::parse($request->SignedOn),
            'LinkedDMSDocID' => $request->LinkedDMSDocID,
            'Remarks' => $request->Remarks,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'IsActive' => 1
        ]);

        return redirect()->route('legal.documents.execution_logs.index', $documentId)
                         ->with('success', 'Execution Log added successfully.');
    }

    public function show($documentId, $id)
    {
        $document = LegalDocument::findOrFail($documentId);
        $log = LegalExecutionLog::where('LegalDocumentID', $documentId)->findOrFail($id);

        return view('legal.execution_logs.show', compact('document', 'log'));
    }

    public function edit($documentId, $id)
    {
        $document = LegalDocument::findOrFail($documentId);
        $log = LegalExecutionLog::where('LegalDocumentID', $documentId)->findOrFail($id);

        return view('legal.execution_logs.edit', compact('document', 'log'));
    }

    public function update(Request $request, $documentId, $id)
    {
        $request->validate([
            'SignedBy' => 'required|string',
            'SignedOn' => 'required|date',
            'Remarks' => 'nullable|string',
        ]);

        $log = LegalExecutionLog::where('LegalDocumentID', $documentId)->findOrFail($id);

        $log->update([
            'SignedBy' => $request->SignedBy,
            'SignedOn' => Carbon::parse($request->SignedOn),
            'LinkedDMSDocID' => $request->LinkedDMSDocID,
            'Remarks' => $request->Remarks,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.documents.execution_logs.index', $documentId)
                         ->with('success', 'Execution Log updated successfully.');
    }

    public function destroy($documentId, $id)
    {
        $log = LegalExecutionLog::where('LegalDocumentID', $documentId)->findOrFail($id);
        $log->update([
            'IsActive' => 0,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.documents.execution_logs.index', $documentId)
                         ->with('success', 'Execution Log archived.');
    }
}
