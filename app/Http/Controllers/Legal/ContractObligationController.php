<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Legal\ContractObligation;
use App\Models\Legal\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContractObligationController extends Controller
{
    public function index($documentId)
    {
        $document = LegalDocument::findOrFail($documentId);
        $obligations = ContractObligation::where('LegalDocumentID', $documentId)
            ->where('IsActive', 1)
            ->get();

        return view('legal.obligations.index', compact('document', 'obligations'));
    }

    public function create($documentId)
    {
        $document = LegalDocument::findOrFail($documentId);
        $users = User::all();

        return view('legal.obligations.create', compact('document', 'users'));
    }

    public function store(Request $request, $documentId)
    {
        $request->validate([
            'ObligationTitle' => 'required|string|max:255',
            'ObligationType' => 'nullable|string|max:100',
            'DueDate' => 'nullable|date',
            'Status' => 'required|string|max:50',
            'AssignedTo' => 'nullable|exists:users,id',
            'Remarks' => 'nullable|string|max:1000',
        ]);

        ContractObligation::create([
            'LegalDocumentID' => $documentId,
            'ObligationTitle' => $request->ObligationTitle,
            'ObligationType' => $request->ObligationType,
            'DueDate' => $request->DueDate,
            'Status' => $request->Status,
            'AssignedTo' => $request->AssignedTo,
            'Remarks' => $request->Remarks,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'IsActive' => 1,
        ]);

        return redirect()->route('legal.documents.obligations.index', $documentId)->with('success', 'Obligation created.');
    }

    public function edit($id)
    {
        $obligation = ContractObligation::findOrFail($id);
        $document = $obligation->document;
        $users = User::all();

        return view('legal.obligations.edit', compact('obligation', 'document', 'users'));
    }

    public function update(Request $request, $id)
    {
        $obligation = ContractObligation::findOrFail($id);

        $request->validate([
            'ObligationTitle' => 'required|string|max:255',
            'ObligationType' => 'nullable|string|max:100',
            'DueDate' => 'nullable|date',
            'Status' => 'required|string|max:50',
            'AssignedTo' => 'nullable|exists:users,id',
            'Remarks' => 'nullable|string|max:1000',
        ]);

        $obligation->update([
            'ObligationTitle' => $request->ObligationTitle,
            'ObligationType' => $request->ObligationType,
            'DueDate' => $request->DueDate,
            'Status' => $request->Status,
            'AssignedTo' => $request->AssignedTo,
            'Remarks' => $request->Remarks,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.documents.obligations.index', $obligation->LegalDocumentID)->with('success', 'Obligation updated.');
    }

    public function destroy($id)
    {
        $obligation = ContractObligation::findOrFail($id);
        $obligation->IsActive = 0;
        $obligation->ModifiedBy = Auth::id();
        $obligation->ModifiedOn = now();
        $obligation->save();

        return redirect()->route('legal.documents.obligations.index', $obligation->LegalDocumentID)->with('success', 'Obligation archived.');
    }
}
