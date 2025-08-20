<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalDocumentController extends Controller
{
    public function index()
    {
        // $documents = LegalDocument::where('IsActive', 1)->latest('CreatedOn')->get();
        return view('legal.documents.index');
    }

    public function create()
    {
        return view('legal.documents.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'DocumentTitle' => 'required',
            'DocumentType' => 'required',
            'SourceModule' => 'required',
        ]);

        LegalDocument::create([
            'DocumentTitle' => $request->DocumentTitle,
            'DocumentType' => $request->DocumentType,
            'SourceModule' => $request->SourceModule,
            'SourceID' => $request->SourceID,
            'LinkedDMSDocID' => $request->LinkedDMSDocID,
            'Remarks' => $request->Remarks,
            'ReviewStatus' => 'Draft',
            'ExecutionStatus' => 'Pending',
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.documents.index')->with('success', 'Document registered.');
    }

    public function show($id)
    {
        $document = LegalDocument::findOrFail($id);
        return view('legal.documents.show', compact('document'));
    }

    public function edit($id)
    {
        $document = LegalDocument::findOrFail($id);
        return view('legal.documents.edit', compact('document'));
    }

    public function update(Request $request, $id)
    {
        $document = LegalDocument::findOrFail($id);
        $document->update(array_merge($request->all(), [
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]));

        return redirect()->route('legal.documents.index')->with('success', 'Document updated.');
    }

    public function destroy($id)
    {
        LegalDocument::where('ID', $id)->update(['IsActive' => 0]);
        return redirect()->route('legal.documents.index')->with('success', 'Document archived.');
    }
}

