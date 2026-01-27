<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\Approval\CodeDetail;
use App\Models\Core\Module;
use App\Models\Finance\FinanceModuleTransactions;
use App\Models\Legal\LegalDocument;
use App\Models\Legal\LegalTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LegalDocumentController extends Controller
{
    public function index(Request $request)
    {
        $query = LegalDocument::query()
            ->select([
                'Id', 'DocumentTitle', 'DocumentType', 'SourceModule', 'SourceID',
                'ReviewStatus', 'ExecutionStatus', 'LinkedDMSDocID', 'DueDate', 'ExpiryDate',
                'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn',
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
                'DispatchDate', 'SignOffDate', 'Remarks', 'DueDate', 'ExpiryDate',
                'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'ReviewedBy', 'ReviewedOn',
            ])
            ->findOrFail($id);

        return view('legal.documents.show', compact('doc'));
    }

    public function create()
    {
        $execStatuses = CodeDetail::where('CodeID', 'LegalExecutionStatusType')->get();
        $docTypes = CodeDetail::where('CodeID', 'LegalDocumentType')->get();
        $moduleIds = FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID', 'Name')->whereIn('ModuleID', $moduleIds)
            ->where('ParentID', null)
            ->orderBy('Name', 'asc')->get();

        // Get all clauses for the clause library
        $clauses = \App\Models\Legal\LegalClause::query()
            ->select('Id', 'Title', 'ClauseType', 'Content', 'Version')
            ->orderBy('Title')
            ->get();

        return view('legal.documents.create', compact('execStatuses', 'modules', 'docTypes', 'clauses'));
    }

    public function store(Request $request)
    {
        // Determine validation rules based on creation mode
        $mode = $request->input('creation_mode', 'upload');

        $rules = [
            'DocumentTitle' => [
                'required',
                'string',
                'max:255',
                Rule::unique('t_LegalDocuments', 'DocumentTitle')
                    ->where(fn ($q) => $q->whereNull('DeletedOn')), // ignore soft-deleted rows
            ],
            'DocumentType' => ['required', 'string', 'max:100'],
            'SourceModule' => ['required', 'string', 'max:100'],
            'SourceID' => ['nullable', 'integer'],
            'Remarks' => ['nullable', 'string'],
            'DueDate' => ['nullable', 'date'],
            'ExpiryDate' => ['nullable', 'date', 'after_or_equal:DueDate'],
            'creation_mode' => ['required', 'in:upload,template'],
        ];

        // Conditional validation based on mode
        if ($mode === 'upload') {
            $rules['LinkedDMSDocID'] = ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'];
        } else {
            $rules['template_id'] = ['required', 'integer', 'exists:t_LegalTemplates,Id'];
            $rules['document_body'] = ['required', 'string']; // Edited template content
            $rules['selected_clause_ids'] = ['nullable', 'string']; // JSON array of clause IDs
        }

        $validated = $request->validate($rules);

        $userId = Auth::id();

        try {
            DB::beginTransaction();

            $moduleName = Module::where('ModuleID', $validated['SourceModule'])->pluck('Name')->first();
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
                'DueDate' => $validated['DueDate'] ?? null,
                'ExpiryDate' => $validated['ExpiryDate'] ?? null,

                'ReviewedBy' => $userId,
                'ReviewedOn' => now(),

                'Remarks' => $validated['Remarks'] ?? null,
                'IsActive' => 1,

                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            // Handle file upload/generation based on mode
            if ($mode === 'upload') {
                // Original upload workflow
                if ($request->hasFile('LinkedDMSDocID')) {
                    $uploadedDocument = $doc->newDocument(
                        ModulesEnum::Legal,
                        $request->file('LinkedDMSDocID'),
                        [PermissionEnum::ContractCreate],
                        Auth::user()
                    );

                    $doc->LinkedDMSDocID = $uploadedDocument->Id;
                    $doc->save();
                }
            } else {
                // Template-based document generation with edited content
                $template = LegalTemplate::findOrFail($validated['template_id']);

                // Get edited document body
                $documentBody = $validated['document_body'];

                // Get selected clause IDs
                $clauseIds = json_decode($validated['selected_clause_ids'] ?? '[]', true);
                $clauseIds = array_values(array_unique(array_map('intval', array_filter($clauseIds))));

                // Fetch selected clauses
                $clauses = collect();
                if (! empty($clauseIds)) {
                    $clauses = \App\Models\Legal\LegalClause::query()
                        ->whereIn('Id', $clauseIds)
                        ->get()
                        ->keyBy('Id');
                }

                // Generate PDF from edited content
                $pdfHtml = $this->generatePdfFromEditedContent(
                    $validated['DocumentTitle'],
                    $template->Title,
                    $template->Version,
                    $documentBody,
                    $clauseIds,
                    $clauses
                );

                $pdf = Pdf::loadHTML($pdfHtml)->setPaper('A4', 'portrait');
                $pdfBinary = $pdf->output();

                // Save to temp file
                $safeName = Str::slug($validated['DocumentTitle'], '_') . '.pdf';
                $tmpPath = 'tmp/' . uniqid('doc_', true) . '_' . $safeName;
                Storage::disk('local')->put($tmpPath, $pdfBinary);

                $absolute = Storage::disk('local')->path($tmpPath);
                $uploaded = new UploadedFile(
                    $absolute,
                    $safeName,
                    'application/pdf',
                    null,
                    true
                );

                // Upload to DMS
                $uploadedDocument = $doc->newDocument(
                    ModulesEnum::Legal,
                    $uploaded,
                    [PermissionEnum::ContractCreate, PermissionEnum::ContractView],
                    Auth::user()
                );

                $doc->LinkedDMSDocID = $uploadedDocument->Id;
                $doc->save();

                // Cleanup temp file
                Storage::disk('local')->delete($tmpPath);
            }

            // Auto-create Legal Obligation if DueDate is provided
            if (! empty($validated['DueDate'])) {
                \App\Models\Legal\LegalObligation::create([
                    'Title' => $validated['DocumentTitle'],
                    'SourceType' => $validated['DocumentType'],
                    'DueDate' => $validated['DueDate'],
                    'ExpiryDate' => $validated['ExpiryDate'] ?? null,
                    'Status' => 'Pending',
                    'Description' => 'Auto-created from Legal Document: ' . $validated['DocumentTitle'] . ' (ID: ' . $doc->Id . ')',
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
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
                'trace' => $e->getTraceAsString(),
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
        $docTypes = CodeDetail::where('CodeID', 'LegalDocumentType')->get();
        $sources = ['Legal', 'Procurement', 'Property', 'HR', 'Insurance'];
        //$reviewStatuses = ['Draft', 'In Review', 'Approved', 'Rejected'];
        $execStatuses = CodeDetail::where('CodeID', 'LegalExecutionStatusType')->get();
        $moduleIds = FinanceModuleTransactions::distinct()->pluck('ModuleID')->toArray();
        $modules = Module::select('ModuleID', 'Name')->whereIn('ModuleID', $moduleIds)
            ->where('ParentID', null)
            ->orderBy('Name', 'asc')->get();

        // Get all clauses for the clause library (for template mode)
        $clauses = \App\Models\Legal\LegalClause::query()
            ->select('Id', 'Title', 'ClauseType', 'Content', 'Version')
            ->orderBy('Title')
            ->get();

        // If you want a dropdown for SourceID (FK to t_Modules), uncomment:
        // $modules = Module::select('ModuleID as id','ModuleName as name')
        //     ->orderBy('ModuleName')->get();

        return view('legal.documents.edit', [
            'doc' => $doc,
            'docTypes' => $docTypes,
            'sources' => $sources,
            //'reviewStatuses' => $reviewStatuses,
            'execStatuses' => $execStatuses,
            'modules' => $modules,
            'clauses' => $clauses,
        ]);
    }

    public function update(Request $request, $id)
    {
        $doc = LegalDocument::findOrFail($id);

        // Determine validation rules based on creation mode
        $mode = $request->input('creation_mode', 'upload');

        $rules = [
            'DocumentTitle' => [
                'required', 'string', 'max:255',
            ],
            'DocumentType' => ['required', 'string', 'max:100'],
            'SourceModule' => ['required', 'string', 'max:100'],
            'SourceID' => ['nullable', 'integer'],
            'Remarks' => ['nullable', 'string'],
            'DueDate' => ['nullable', 'date'],
            'ExpiryDate' => ['nullable', 'date', 'after_or_equal:DueDate'],
            'ReviewStatus' => ['nullable', 'string', 'max:50'],
            'ExecutionStatus' => ['nullable', 'string', 'max:50'],
            'DispatchDate' => ['nullable', 'date'],
            'SignOffDate' => ['nullable', 'date'],
            'DetachOldPivots' => ['sometimes', 'boolean'],
            'creation_mode' => ['required', 'in:upload,template'],
        ];

        // Conditional validation based on mode
        if ($mode === 'upload') {
            $rules['LinkedDMSDocID'] = ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'];
        } else {
            $rules['template_id'] = ['required', 'integer', 'exists:t_LegalTemplates,Id'];
            $rules['document_body'] = ['required', 'string'];
            $rules['selected_clause_ids'] = ['nullable', 'string'];
        }

        $validated = $request->validate($rules);

        try {
            DB::beginTransaction();

            $moduleName = Module::where('ModuleID', $validated['SourceModule'])->pluck('Name')->first();
            // Update normal fields
            $previousDueDate = $doc->DueDate; // Store old value to check if changed
            $doc->fill([
                'DocumentTitle' => $validated['DocumentTitle'],
                'DocumentType' => $validated['DocumentType'],
                'SourceModule' => $moduleName,
                'SourceID' => $validated['SourceModule'],
                'Remarks' => $validated['Remarks'] ?? $doc->Remarks,
                'DueDate' => $validated['DueDate'] ?? $doc->DueDate,
                'ExpiryDate' => $validated['ExpiryDate'] ?? $doc->ExpiryDate,
                'ReviewStatus' => $validated['ReviewStatus'] ?? $doc->ReviewStatus,
                'ExecutionStatus' => $validated['ExecutionStatus'] ?? $doc->ExecutionStatus,
                'DispatchDate' => $validated['DispatchDate'] ?? $doc->DispatchDate,
                'SignOffDate' => $validated['SignOffDate'] ?? $doc->SignOffDate,
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ])->save();

            // Handle file upload/generation based on mode
            if ($mode === 'upload') {
                // Original upload workflow - only if file is provided
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
            } else {
                // Template-based document generation with edited content
                $template = LegalTemplate::findOrFail($validated['template_id']);

                // Get edited document body
                $documentBody = $validated['document_body'];

                // Get selected clause IDs
                $clauseIds = json_decode($validated['selected_clause_ids'] ?? '[]', true);
                $clauseIds = array_values(array_unique(array_map('intval', array_filter($clauseIds))));

                // Fetch selected clauses
                $clauses = collect();
                if (! empty($clauseIds)) {
                    $clauses = \App\Models\Legal\LegalClause::query()
                        ->whereIn('Id', $clauseIds)
                        ->get()
                        ->keyBy('Id');
                }

                // Generate PDF from edited content
                $pdfHtml = $this->generatePdfFromEditedContent(
                    $validated['DocumentTitle'],
                    $template->Title,
                    $template->Version,
                    $documentBody,
                    $clauseIds,
                    $clauses
                );

                $pdf = Pdf::loadHTML($pdfHtml)->setPaper('A4', 'portrait');
                $pdfBinary = $pdf->output();

                // Save to temp file
                $safeName = Str::slug($validated['DocumentTitle'], '_') . '_' . time() . '.pdf';
                $tmpPath = 'tmp/' . uniqid('doc_', true) . '_' . $safeName;
                Storage::disk('local')->put($tmpPath, $pdfBinary);

                $absolute = Storage::disk('local')->path($tmpPath);
                $uploaded = new UploadedFile(
                    $absolute,
                    $safeName,
                    'application/pdf',
                    null,
                    true
                );

                // Upload to DMS (creates new document, doesn't replace)
                $uploadedDocument = $doc->newDocument(
                    ModulesEnum::Legal,
                    $uploaded,
                    [PermissionEnum::ContractCreate, PermissionEnum::ContractView],
                    Auth::user()
                );

                // Update FK to latest DMS doc
                $doc->LinkedDMSDocID = $uploadedDocument->Id;
                $doc->save();

                // Cleanup temp file
                Storage::disk('local')->delete($tmpPath);
            }

            // Auto-create Legal Obligation if DueDate was null and is now provided
            if (empty($previousDueDate) && ! empty($validated['DueDate'])) {
                \App\Models\Legal\LegalObligation::create([
                    'Title' => $validated['DocumentTitle'],
                    'SourceType' => $validated['DocumentType'],
                    'DueDate' => $validated['DueDate'],
                    'ExpiryDate' => $validated['ExpiryDate'] ?? null,
                    'Status' => 'Pending',
                    'Description' => 'Auto-created from Legal Document: ' . $validated['DocumentTitle'] . ' (ID: ' . $doc->Id . ')',
                    'CreatedBy' => Auth::id(),
                    'ModifiedBy' => Auth::id(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('legal.documents.show', $doc->Id)
                ->with('success', 'Document updated and new version added.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Failed to update legal document', [
                'doc_id' => $doc->Id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Could not update document. DB said: ' . $e->getMessage());
        }
    }

    /**
     * Generate HTML content from template for PDF generation
     */
    private function generateDocumentFromTemplate(LegalTemplate $template, string $documentTitle): string
    {
        $templateBody = $template->TemplateBody ?? '';

        // Get clauses in order
        $clausesHtml = '';
        if ($template->clauses && $template->clauses->count() > 0) {
            $clausesHtml .= '<hr style="margin: 20px 0; border: none; border-top: 1px solid #ddd;">';
            $clausesHtml .= '<h2 style="font-size: 16px; font-weight: 700; margin: 0 0 12px 0;">Clauses</h2>';

            foreach ($template->clauses as $i => $clause) {
                $n = $i + 1;
                $title = $clause->pivot->TitleOverride ?: $clause->Title;
                $content = $clause->pivot->ContentOverride ?: $clause->Content;

                $clausesHtml .= "
                <div style=\"margin: 16px 0;\">
                    <div style=\"font-weight: 600; font-size: 13px; margin-bottom: 6px;\">Clause {$n}: " . e($title) . "</div>
                    <div style=\"font-size: 12px; line-height: 1.6;\">" . nl2br(e($content)) . "</div>
                </div>";
            }
        }

        return '
        <!doctype html>
        <html>
          <head>
            <meta charset="utf-8"/>
            <style>
              @page { margin: 24px 28px; }
              body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color: #222; }
            </style>
          </head>
          <body>
            <h1 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700;">' . e($documentTitle) . '</h1>
            <div style="color: #666; font-size: 11px; margin: 0 0 16px 0;">Generated from template: ' . e($template->Title) . ' (v' . e($template->Version) . ')</div>
            <div style="font-size: 12px; line-height: 1.6;">' . $templateBody . '</div>
            ' . $clausesHtml . '
          </body>
        </html>';
    }

    /**
     * Generate PDF HTML from edited document content
     */
    private function generatePdfFromEditedContent(
        string $documentTitle,
        string $templateTitle,
        $templateVersion,
        string $documentBody,
        array $clauseIds,
        $clauseMap
    ): string {
        // No longer outputting clauses section - just the edited document body

        return '
        <!doctype html>
        <html>
          <head>
            <meta charset="utf-8"/>
            <style>
              @page { margin: 24px 28px; }
              body { 
                font-family: DejaVu Sans, Arial, Helvetica, sans-serif; 
                color: #222; 
                font-size: 12px;
                line-height: 1.6;
              }
              
              /* Table styles for CKEditor tables */
              table { 
                width: 100%; 
                border-collapse: collapse; 
                margin: 12px 0;
                page-break-inside: auto;
              }
              table tr { 
                page-break-inside: avoid; 
                page-break-after: auto; 
              }
              table td, table th { 
                border: 1px solid #ddd; 
                padding: 8px; 
                text-align: left;
                vertical-align: top;
              }
              table th { 
                background-color: #f2f2f2; 
                font-weight: bold;
              }
              
              /* List styles */
              ul, ol { 
                margin: 8px 0; 
                padding-left: 24px; 
              }
              li { 
                margin: 4px 0; 
              }
              
              /* Heading styles */
              h1, h2, h3, h4, h5, h6 { 
                margin: 16px 0 8px 0; 
                font-weight: 700;
              }
              h1 { font-size: 20px; }
              h2 { font-size: 18px; }
              h3 { font-size: 16px; }
              h4 { font-size: 14px; }
              
              /* Paragraph styles */
              p { 
                margin: 8px 0; 
              }
              
              /* Other common elements */
              strong, b { font-weight: bold; }
              em, i { font-style: italic; }
              u { text-decoration: underline; }
              
              /* Image styles */
              img { 
                max-width: 100%; 
                height: auto; 
              }
              
              /* Blockquote styles */
              blockquote {
                margin: 12px 0;
                padding: 8px 12px;
                border-left: 4px solid #ddd;
                background: #f9f9f9;
              }
            </style>
          </head>
          <body>
            <h1 style="margin: 0 0 8px 0; font-size: 20px; font-weight: 700;">' . e($documentTitle) . '</h1>
            <div style="color: #666; font-size: 11px; margin: 0 0 16px 0;">Generated from template: ' . e($templateTitle) . ' (v' . e($templateVersion) . ')</div>
            <div>' . $documentBody . '</div>
          </body>
        </html>';
    }

    /**
     * Get full template content by ID for editing
     */
    public function getTemplateById($id)
    {
        $template = LegalTemplate::with('clauses')->findOrFail($id);

        return response()->json([
            'template_body' => $template->TemplateBody,
            'clause_ids' => $template->clauses->pluck('Id')->toArray(),
        ]);
    }

    /**
     * AJAX endpoint: Get templates by document type
     */
    public function getTemplatesByType(Request $request)
    {
        $docType = $request->input('document_type');

        if (! $docType) {
            return response()->json(['templates' => []]);
        }

        $templates = LegalTemplate::query()
            ->where('DocumentType', $docType)
            ->where('IsActive', true)
            ->select('Id', 'Title', 'Version', 'Description', 'DocumentType')
            ->orderBy('Title')
            ->orderByDesc('Version')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->Id,
                    'title' => $template->Title,
                    'version' => $template->Version,
                    'description' => $template->Description,
                ];
            });

        return response()->json(['templates' => $templates]);
    }

    public function destroy($id)
    {

        LegalDocument::where('ID', $id)->update(['IsActive' => 0, 'DeletedBy' => Auth::id()]);
        LegalDocument::find($id)->delete();

        return redirect()->route('legal.documents.index')->with('success', 'Document Deleted.');
    }
}
