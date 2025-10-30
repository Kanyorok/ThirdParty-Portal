<?php

namespace App\Http\Controllers\Legal;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use App\Models\Legal\LegalClause;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Legal\LegalTemplate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class LegalTemplateController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::ContractView, LegalClause::class);

        $q      = trim((string) $request->input('q'));
        $status = (string) $request->input('status', '');
        $type   = (string) $request->input('type', '');

        $query = LegalTemplate::query()
            ->withCount('clauses');

        if ($q !== '') {
            $query->where(function ($sub) use ($q) {
                $sub->where('Title', 'like', "%{$q}%")
                    ->orWhere('DocumentType', 'like', "%{$q}%")
                    ->orWhere('Description', 'like', "%{$q}%")
                    ->orWhere('Version', 'like', "%{$q}%");
            });
        }
        if ($status !== '') {
            $query->where('Status', $status);
        }
        if ($type !== '') {
            $query->where('DocumentType', $type);
        }

        $templates = $query
            ->orderByDesc('CreatedOn')
            ->paginate(15)
            ->withQueryString();

        // For the filters dropdowns
        $types   = LegalTemplate::query()->whereNotNull('DocumentType')->distinct()->orderBy('DocumentType')->pluck('DocumentType');
        $statuses= collect(['DRAFT','ACTIVE','DEPRECATED','ARCHIVED']);

        return view('legal.templates.index', compact('templates','q','status','type','types','statuses'));
    }

    public function show($id)
    {
        $this->authorize(PermissionEnum::ContractView, LegalClause::class);

        $template = LegalTemplate::with('clauses')->findOrFail($id);
        $clauses  = $template->clauses; // already ordered
        return view('legal.templates.show', compact('template', 'clauses'));
    }

    public function create()
    {
        $this->authorize(PermissionEnum::ContractCreate, LegalClause::class);

        $clauses = LegalClause::query()
            ->select('Id', 'Title', 'ClauseType', 'Content', 'Version', 'IsStandard')
            ->orderBy('Title')
            ->get();

        // Use the same document types source as legal documents page
        $docTypes = CodeDetail::where('CodeID', 'LegalDocumentType')->get();

        return view('legal.templates.create', compact('clauses', 'docTypes'));
    }

//    public function store(Request $request)
//    {
//            $request->validate([
//                'TemplateName' => 'required|string|max:255',
//                'DocumentType' => 'required|string|max:100',
//                'Version' => 'required|string|max:20',
//                'Description' => 'nullable|string',
//                'Content' => 'nullable|string',
//            ]);
//
//            LegalTemplate::create([
//                'TemplateName' => $request->TemplateName,
//                'DocumentType' => $request->DocumentType,
//                'Version' => $request->Version,
//                'Description' => $request->Description,
//                'Content' => $request->Content,
//                'CreatedBy' => Auth::Id(),
//                'CreatedOn' => now(),
//                'IsActive' => 1,
//            ]);
//
//        LegalTemplate::create($request->all());
//
//        return redirect()->route('legal.templates.index')->with('success', 'Template created successfully.');
//    }

    public function store(Request $request)
    {
        $this->authorize(PermissionEnum::ContractCreate, LegalClause::class);

        // 1) Validate
        $validated = $request->validate([
            'TemplateName' => ['required', 'string', 'max:255'],
            'DocumentType' => ['nullable', 'string', 'max:100'],
            'Version' => ['nullable', 'string', 'max:50'],
            'Description' => ['nullable', 'string', 'max:500'],
            'TemplateBody' => ['required', 'string'],
            'AttachedClauseIDs' => ['nullable'], // JSON string or array (validated below)
        ]);

        // Unique combo (Title, Version, DocumentType)
        $request->validate([
            'TemplateName' => [
                Rule::unique('t_LegalTemplates', 'Title')
                    ->where(fn($q) => $q
                        ->where('Version', $request->input('Version', 'v1.0'))
                        ->where('DocumentType', $request->input('DocumentType'))
                    ),
            ],
        ], [
            'TemplateName.unique' => 'A template with the same Title, Version and Document Type already exists.',
        ]);

        // 2) Normalize + prepare
        $title = $request->input('TemplateName');
        $docType = $request->input('DocumentType');
        $version = $request->input('Version', 'v1.0');
        $description = $request->input('Description');
        $bodyHtml = $request->input('TemplateBody');

        $attachedRaw = $request->input('AttachedClauseIDs', '[]');
        $attachedIds = is_string($attachedRaw) ? json_decode($attachedRaw, true) : (array)$attachedRaw;
        $attachedIds = array_values(array_unique(array_map('intval', array_filter($attachedIds))));

        // Make sure clauses exist, preserve the given order
        $existingIds = LegalClause::query()->whereIn('Id', $attachedIds)->pluck('Id')->map(fn($i) => (int)$i)->all();
        $orderedClauseIds = array_values(array_intersect($attachedIds, $existingIds));

        // 3) Save everything atomically
        $template = DB::transaction(function () use ($title, $docType, $version, $description, $bodyHtml, $orderedClauseIds) {
            $userId = Auth::id();

            // Create template
            $template = LegalTemplate::create([
                'Title' => $title,
                'DocumentType' => $docType,
                'Version' => $version,
                'Description' => $description,
                'TemplateBody' => $bodyHtml,
                'CreatedBy' => $userId,
                'ModifiedBy' => $userId,
            ]);

            // Attach clauses with positions
            if (!empty($orderedClauseIds)) {
                $pos = 1;
                foreach ($orderedClauseIds as $cid) {
                    $template->clauses()->attach($cid, [
                        'Position' => $pos++,
                        'IsMandatory' => false,
                        'CreatedBy' => $userId,
                        'CreatedOn' => now(),
                        'ModifiedBy' => $userId,
                        'ModifiedOn' => now(),
                    ]);
                }
            }

            // Generate PDF (Template Body + selected clauses as Annex)
            $clauses = collect();
            if (!empty($orderedClauseIds)) {
                $clauses = LegalClause::query()
                    ->whereIn('Id', $orderedClauseIds)
                    ->get()
                    ->keyBy('Id');
            }

            $pdfHtml = $this->composePdfHtml($title, $description, $bodyHtml, $orderedClauseIds, $clauses);

            $pdf = Pdf::loadHTML($pdfHtml)->setPaper('A4', 'portrait');
            $pdfBinary = $pdf->output();

            // Save to a temp path and wrap as UploadedFile for DMS
            $safeName = Str::slug($title ?: 'template', '_') . '_' . str_replace('.', '_', $version) . '.pdf';
            $tmpPath = 'tmp/' . uniqid('tmpl_', true) . '_' . $safeName;
            Storage::disk('local')->put($tmpPath, $pdfBinary);

            $absolute = Storage::disk('local')->path($tmpPath);
            $uploaded = new UploadedFile(
                $absolute,
                $safeName,
                'application/pdf',
                null,
                true // mark as test file so it isn't moved by Symfony validator
            );

            // Store in DMS (adjust enums/permissions to your system)
            // Example: ModulesEnum::Legal and permissions for template create/view
            $document = $template->newDocument(
                ModulesEnum::Legal,
                $uploaded,
                [PermissionEnum::ContractCreate, PermissionEnum::ContractView],
                Auth::user()
            );

            // If your DMS returns an object/id, persist it
            $dmsId = $document->Id ?? $document->id ?? (is_string($document) ? $document : null);
            if ($dmsId) {
                $template->DocumentDMSID = $dmsId;
                $template->save();
            }

            // Cleanup temp file
            Storage::disk('local')->delete($tmpPath);

            return $template;
        });

        return redirect()
            ->route('legal.templates.index')
            ->with('success', 'Template created and archived to DMS successfully.');
    }

    /**
     * Compose simple PDF HTML from template body + selected clauses.
     */
    private function composePdfHtml(string $title = null, ?string $description = null, string $bodyHtml = '', array $orderedClauseIds = [], $clauseMap = null): string
    {
        $header = <<<HTML
        <h1 style="margin:0 0 6px 0; font-size:20px; font-weight:700;">{$this->e($title)}</h1>
        HTML;

        $desc = $description
            ? '<div style="color:#555; font-size:12px; margin:0 0 12px 0;">' . $this->e($description) . '</div>'
            : '';

        $body = '<div style="font-size:12px; line-height:1.5;">' . $bodyHtml . '</div>';

        $annex = '';
        if (!empty($orderedClauseIds) && $clauseMap && $clauseMap->count()) {
            $items = '';
            foreach ($orderedClauseIds as $i => $cid) {
                $c = $clauseMap->get($cid);
                if (!$c) continue;
                $n = $i + 1;
                $items .= '
                  <div style="margin:12px 0;">
                    <div style="font-weight:600; font-size:13px; margin-bottom:4px;">' . $this->e("Clause {$n}: {$c->Title}") . '</div>
                    <div style="font-size:12px; line-height:1.5;">' . nl2br(e($c->Content)) . '</div>
                  </div>';
            }

            $annex = '
              <hr style="margin:18px 0; border:none; border-top:1px solid #ddd;">
              <h2 style="font-size:14px; font-weight:700; margin:0 0 8px 0;">Annex: Selected Clauses</h2>
              ' . $items;
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
            ' . $header . $desc . $body . $annex . '
          </body>
        </html>';
    }

    private function e(?string $s): string
    {
        return e($s ?? '');
    }
}
