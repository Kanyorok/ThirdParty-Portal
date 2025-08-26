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
        return view('legal.documents.index');
    }

    public function create()
    {
        return view('legal.documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'DocumentTitle'   => ['required', 'string', 'max:255'],
            'DocumentType'    => ['required', 'string', 'max:100'],
            'SourceModule'    => ['required', 'string', 'max:100'],
            'SourceID'        => ['nullable', 'integer'],
            'LinkedDMSDocID'  => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'Remarks'         => ['nullable', 'string'],
        ]);

        $userId = Auth::id();

        try {
            DB::beginTransaction();

            $doc = LegalDocument::create([
                'DocumentTitle'   => $validated['DocumentTitle'],
                'DocumentType'    => $validated['DocumentType'],
                'SourceModule'    => $validated['SourceModule'],
                'SourceID'        => $validated['SourceID'] ?? 1,
                'LinkedDMSDocID'  => 10,
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

            if ($request->hasFile('LinkedDMSDocID')) {
                $doc->newDocument(
                    ModulesEnum::Legal,
                    $request->file('LinkedDMSDocID'),
                    [PermissionEnum::ContractCreate],
                    Auth::user()
                );
            }

            DB::commit();

            return redirect()->route('legal.documents.index')->with('success', 'Document registered.');

        } catch (\Throwable $e) {
            DB::rollBack();
            \Log::error('Failed to save legal document', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withInput()->with('error', 'Could not save document. DB said: ' . $e->getMessage());
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
    public function index(Request $request)
    {
        $query = LegalDocument::query()
            ->select([
                'Id','DocumentTitle','DocumentType','SourceModule','SourceID',
                'ReviewStatus','ExecutionStatus','LinkedDMSDocID',
                'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'
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
            'q'      => $request->input('q'),
            'type'   => $request->input('type'),
            'source' => $request->input('source'),
            'review' => $request->input('review'),
            'exec'   => $request->input('exec'),
            'from'   => $request->input('from'),
            'to'     => $request->input('to'),
        ];

        return view('legal.documents.index', compact('documents', 'filters'));
    }

    public function show(int $id)
    {
        $doc =LegalDocument::query()
            ->with(['createdBy:Id,Name', 'modifiedBy:Id,Name']) // add reviewedBy if you create it
            ->select([
                'Id','DocumentTitle','DocumentType','SourceModule','SourceID',
                'LinkedDMSDocID','ReviewStatus','ExecutionStatus',
                'DispatchDate','SignOffDate','Remarks',
                'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn','ReviewedBy','ReviewedOn'
            ])
            ->findOrFail($id);

        return view('legal.documents.show', compact('doc'));
    }

    public function create()
    {
        return view('legal.documents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
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
                'SourceID'        =>800000 ?? 1,  // fallback to 1 for now
                'LinkedDMSDocID'  => 10, // Replace with real DMS ID after upload
                'ReviewStatus'    => 'Draft',
                'ExecutionStatus' => 'Pending',
                'DispatchDate'    => null,
                'SignOffDate'     => null,

                'ReviewedBy'      => $userId,
                'ReviewedOn'      => now(),

                'Remarks'         => $validated['Remarks'] ?? null,
                'IsActive'        => 1,

                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy'      => Auth::id(),
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
            return $e->getMessage();
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
        $types          = ['Contract','Lease','NDA','MOU'];
        $sources        = ['Legal','Procurement','Property','HR','Insurance'];
        $reviewStatuses = ['Draft','In Review','Approved','Rejected'];
        $execStatuses   = ['Pending','Signed','Archived'];

        // If you want a dropdown for SourceID (FK to t_Modules), uncomment:
        // $modules = Module::select('ModuleID as id','ModuleName as name')
        //     ->orderBy('ModuleName')->get();

        return view('legal.documents.edit', [
            'doc'            => $doc,
            'types'          => $types,
            'sources'        => $sources,
            'reviewStatuses' => $reviewStatuses,
            'execStatuses'   => $execStatuses,
            // 'modules'      => $modules,
        ]);
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

