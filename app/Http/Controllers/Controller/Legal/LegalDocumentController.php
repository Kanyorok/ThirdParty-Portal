<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Legal\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        return$validated = $request->validate([
            'DocumentTitle'   => ['required', 'string', 'max:255'],
            'DocumentType'    => ['required', 'string', 'max:100'],
            'SourceModule'    => ['required', 'string', 'max:100'],
            'SourceID'        => ['nullable', 'integer'],
            'LinkedDMSDocID'  => ['required', 'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'Remarks'         => ['nullable', 'string'],
        ]);

        $userId = Auth::id();

        try {
            DB::beginTransaction();

            $doc = LegalDocument::create([
                'DocumentTitle'   => $validated['DocumentTitle'],
                'DocumentType'    => $validated['DocumentType'],
                'SourceModule'    => $validated['SourceModule'],
                'SourceID'        => $validated['SourceID'] ?? 1,  // fallback to 1 for now
                'LinkedDMSDocID'  => 10, // Replace with real DMS ID after upload
                'ReviewStatus'    => 'Draft',
                'ExecutionStatus' => 'Pending',
                'DispatchDate'    => null,
                'SignOffDate'     => null,

                'ReviewedBy'      => $userId,
                'ReviewedOn'      => now(),

                'Remarks'         => $validated['Remarks'] ?? null,
                'IsActive'        => 1,

                'CreatedBy'       => $userId,
                'CreatedOn'       => now(),
                'ModifiedBy'      => $userId,
                'ModifiedOn'      => now(),
            ]);

            // Upload Evidence to E-DMS
            if ($request->hasFile('LinkedDMSDocID')) {
                $doc->newDocument(
                    ModulesEnum::Legal,
                    $request->file('LinkedDMSDocID'),
                    [PermissionEnum::ContractCreate],
                    Auth::user()
                );
            }

            DB::commit();

            return redirect()
                ->route('legal.documents.index')
                ->with('success', 'Document registered.');

        } catch (\Throwable $e) {
            DB::rollBack();
            // log the actual DB error for debugging
            \Log::error('Failed to save legal document', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not save document. DB said: ' . $e->getMessage());
        }
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

