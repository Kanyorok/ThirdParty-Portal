<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Core\Module;
use App\Models\Finance\FinanceModuleTransactions;
use App\Models\Legal\LegalDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class LegalDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalDocument::query()
            ->select([
                'Id', 'DocumentTitle', 'DocumentType', 'SourceModule', 'SourceID',
                'ReviewStatus', 'ExecutionStatus', 'LinkedDMSDocID',
                'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn'
            ]);

        // Filters
        if ($request->filled('q')) {
            $q = trim($request->input('q'));
            $query->where(function ($w) use ($q) {
                $w->where('DocumentTitle', 'like', "%{$q}%")
                    ->orWhere('DocumentType', 'like', "%{$q}%")
                    ->orWhere('SourceModule', 'like', "%{$q}%");
            });
        }
        if ($request->filled('type')) {
            $query->where('DocumentType', $request->type);
        }
        if ($request->filled('source')) {
            $query->where('SourceModule', $request->source);
        }
        if ($request->filled('review')) {
            $query->where('ReviewStatus', $request->review);
        }
        if ($request->filled('exec')) {
            $query->where('ExecutionStatus', $request->exec);
        }

        // Date range (CreatedOn)
        if ($request->filled('from')) {
            $query->whereDate('CreatedOn', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('CreatedOn', '<=', $request->input('to'));
        }

        $documents = $query
            ->orderByDesc('CreatedOn')
            ->paginate(15)
            ->appends($request->query());

        // Pass selected filters back to view for sticky form
        $filters = [
            'q' => $request->input('q'),
            'type' => $request->input('type'),
            'source' => $request->input('source'),
            'review' => $request->input('review'),
            'exec' => $request->input('exec'),
            'from' => $request->input('from'),
            'to' => $request->input('to'),
        ];

        return view('legal.documents.index', compact('documents', 'filters'));
    }

    public function show(int $id)
    {
        $doc = LegalDocument::query()
            ->with(['createdBy:Id,Name', 'modifiedBy:Id,Name']) // add reviewedBy if you create it
            ->select([
                'Id', 'DocumentTitle', 'DocumentType', 'SourceModule', 'SourceID',
                'LinkedDMSDocID', 'ReviewStatus', 'ExecutionStatus',
                'DispatchDate', 'SignOffDate', 'Remarks',
                'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'ReviewedBy', 'ReviewedOn'
            ])
            ->findOrFail($id);

        return view('legal.documents.show', compact('doc'));
    }

    public function create()
    {
        $execStatuses = CodeDetail::where('CodeID', 'LegalExecutionStatusType')->get();
        $docTypes=CodeDetail::where('CodeID', 'LegalDocumentType')->get();
        $moduleIds=FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID','Name')->whereIn('ModuleID', $moduleIds)
            ->where('ParentID', null)
            ->orderBy('Name', 'asc')->get();

        return view('legal.documents.create',compact('execStatuses','modules','docTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'DocumentTitle' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_LegalDocuments', 'DocumentTitle')
                    ->where(fn($q) => $q->whereNull('DeletedOn')), // ignore soft-deleted rows
            ],
            'DocumentType' => ['required', 'string', 'max:100'],
            'SourceModule' => ['required', 'string', 'max:100'],
            'SourceID' => ['nullable', 'integer'],
            'LinkedDMSDocID' => ['required', 'file',
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'Remarks' => ['nullable', 'string'],
        ]);

        $userId = Auth::id();

        try {
            DB::beginTransaction();

            $moduleName=Module::where('ModuleID',$validated['SourceModule'])->pluck('Name')->first();
            $doc = LegalDocument::create([
                'DocumentTitle' => $validated['DocumentTitle'],
                'DocumentType' => $validated['DocumentType'],
                'SourceModule' => $moduleName,
                'SourceID' => $validated['SourceModule'],
                'LinkedDMSDocID' => 10, //Removed this part
                'ReviewStatus' => 'Draft',
                'ExecutionStatus' => 'Pending',
                'DispatchDate' => null,
                'SignOffDate' => null,

                'ReviewedBy' => $userId,
                'ReviewedOn' => now(),

                'Remarks' => $validated['Remarks'] ?? null,
                'IsActive' => 1,

                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Upload Evidence to E-DMS
            if ($request->hasFile('LinkedDMSDocID')) {
                $uploadedDocument = $doc->newDocument(
                    ModulesEnum::Legal,
                    $request->file('LinkedDMSDocID'),
                    [PermissionEnum::ContractCreate],
                    Auth::user()
                );

                // Post teh doc id into Legal
                $doc->LinkedDMSDocID = $uploadedDocument->Id; // DMS Document PK
                $doc->save();
            }

            DB::commit();

            return redirect()
                ->route('legal.documents.index')
                ->with('success', 'Document registered.');

        } catch (\Throwable $e) {
            DB::rollBack();
            //return $e->getMessage();
            // log the actual DB error for debugging
            Log::error('Failed to save legal document', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not save document. DB said: ' . $e->getMessage());
        }
    }

//    public function show($id)
//    {
//        $document = LegalDocument::findOrFail($id);
//        return view('legal.documents.show', compact('document'));
//    }

    public function edit(int $id)
    {
        // Eager-load users to show names in the header or helper text if needed
        $doc = LegalDocument::query()
            ->with([
                'createdBy:Id,Name',
                'modifiedBy:Id,Name',
                // 'reviewedBy:Id,Name', // uncomment if you add this relation
            ])
            ->findOrFail($id);

        // Option sets for your selects (same as in create/edit blades)
        $docTypes=CodeDetail::where('CodeID', 'LegalDocumentType')->get();
        $sources = ['Legal', 'Procurement', 'Property', 'HR', 'Insurance'];
        //$reviewStatuses = ['Draft', 'In Review', 'Approved', 'Rejected'];
        $execStatuses = CodeDetail::where('CodeID', 'LegalExecutionStatusType')->get();
        $moduleIds=FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID','Name')->whereIn('ModuleID', $moduleIds)
            ->where('ParentID', null)
            ->orderBy('Name', 'asc')->get();

        // If you want a dropdown for SourceID (FK to t_Modules), uncomment:
        // $modules = Module::select('ModuleID as id','ModuleName as name')
        //     ->orderBy('ModuleName')->get();

        return view('legal.documents.edit', [
            'doc' => $doc,
            'docTypes' => $docTypes,
            'sources' => $sources,
            //'reviewStatuses' => $reviewStatuses,
            'execStatuses' => $execStatuses,
            'modules'      => $modules,
        ]);
    }

    public function update(Request $request, $id)
    {
        $doc = LegalDocument::findOrFail($id);

        // Validate (DocumentTitle stays unique, ignoring this record; LinkedDMSDocID is OPTIONAL here)
        $validated = $request->validate([
            'DocumentTitle' => [
                'required', 'string', 'max:255'
            ],
            'DocumentType'    => ['required', 'string', 'max:100'],
            'SourceModule'    => ['required', 'string', 'max:100'],
            'SourceID'        => ['nullable', 'integer'],
            'LinkedDMSDocID'  => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'Remarks'         => ['nullable', 'string'],
            'ReviewStatus'    => ['nullable', 'string', 'max:50'],
            'ExecutionStatus' => ['nullable', 'string', 'max:50'],
            'DispatchDate'    => ['nullable', 'date'],
            'SignOffDate'     => ['nullable', 'date'],
            // Optional toggle if you ever want to drop older pivots:
            'DetachOldPivots' => ['sometimes','boolean'],
        ]);

        try {
            DB::beginTransaction();

            $moduleName=Module::where('ModuleID',$validated['SourceModule'])->pluck('Name')->first();
            // Update normal fields
            $doc->fill([
                'DocumentTitle'   => $validated['DocumentTitle'],
                'DocumentType'    => $validated['DocumentType'],
                'SourceModule'    =>$moduleName,
                'SourceID'        => $validated['SourceModule'],
                'Remarks'         => $validated['Remarks'] ?? $doc->Remarks,
                'ReviewStatus'    => $validated['ReviewStatus'] ?? $doc->ReviewStatus,
                'ExecutionStatus' => $validated['ExecutionStatus'] ?? $doc->ExecutionStatus,
                'DispatchDate'    => $validated['DispatchDate'] ?? $doc->DispatchDate,
                'SignOffDate'     => $validated['SignOffDate'] ?? $doc->SignOffDate,
                'ModifiedBy'      => Auth::id(),
                'ModifiedOn'      => now(),
            ])->save();

            // 2) If a new file was uploaded, create a NEW DMS document and point to it
            if ($request->hasFile('LinkedDMSDocID')) {
                $uploadedDocument = $doc->newDocument(
                    ModulesEnum::Legal,
                    $request->file('LinkedDMSDocID'),
                    [PermissionEnum::ContractCreate],
                    Auth::user()
                );

                // Update FK to latest DMS doc
                $doc->LinkedDMSDocID = $uploadedDocument->Id;
                $doc->save();

                // (Optional) If you want to keep ONLY the latest link in the pivot:
                if ($request->boolean('DetachOldPivots', false)) {
                    \App\Models\DMS\DocumentRelation::where('Related', $doc::getPrimaryKey())
                        ->where('RelatedID', $doc->getKey())
                        ->where('DocumentId', '!=', $uploadedDocument->Id)
                        ->delete();
                }
            }

            DB::commit();

            return redirect()
                ->route('legal.documents.show', $doc->Id)
                ->with('success', 'Document updated.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update legal document', [
                'doc_id' => $doc->Id,
                'error'  => $e->getMessage(),
                'trace'  => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not update document. DB said: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {

        LegalDocument::where('ID', $id)->update(['IsActive' => 0,'DeletedBy' => Auth::id()]);
        LegalDocument::find($id)->delete();
        return redirect()->route('legal.documents.index')->with('success', 'Document Deleted.');
    }
}

