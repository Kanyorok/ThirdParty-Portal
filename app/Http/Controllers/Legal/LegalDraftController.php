<?php


namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalDraft;
use App\Models\Legal\LegalClause;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalDraftController extends Controller
{
    public function index()
    {
        $drafts = LegalDraft::orderByDesc('CreatedOn')->get();
        return view('legal.drafts.index', compact('drafts'));
    }

    public function create()
    {
        return view('legal.drafts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'DraftTitle' => 'required|string|max:255',
            'DocumentType' => 'nullable|string|max:100',
            'Content' => 'nullable|string',
        ]);

        LegalDraft::create([
            'DraftTitle' => $request->DraftTitle,
            'Description' => $request->Description,
            'DocumentType' => $request->DocumentType,
            'Content' => $request->Content,
            'Status' => 'Draft',
            'LinkedTemplateID' => $request->LinkedTemplateID,
            'CreatedBy' => Auth::id(),
        ]);

        return redirect()->route('legal.drafts.index')->with('success', 'Draft created successfully.');
    }

    public function edit($id)
    {
        $draft = LegalDraft::findOrFail($id);
        return view('legal.drafts.edit', compact('draft'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'DraftTitle' => 'required|string|max:255',
            'DocumentType' => 'nullable|string|max:100',
            'Content' => 'nullable|string',
        ]);

        $draft = LegalDraft::findOrFail($id);
        $draft->update([
            'DraftTitle' => $request->DraftTitle,
            'Description' => $request->Description,
            'DocumentType' => $request->DocumentType,
            'Content' => $request->Content,
        ]);

        return redirect()->route('legal.drafts.index')->with('success', 'Draft updated successfully.');
    }

    public function show($id)
    {
        $draft = LegalDraft::findOrFail($id);
        return view('legal.drafts.show', compact('draft'));
    }

    // Clause fetch API for JS sidebar
public function fetchClauses(Request $request)
{
    $q = $request->query('q');

    $clauses = DB::table('t_LegalClauses')
        ->where('IsActive', 1)
        ->where(function ($query) use ($q) {
            $query->where('Title', 'like', "%$q%")
                  ->orWhere('Content', 'like', "%$q%");
        })
        ->orderBy('Title')
        ->limit(10)
        ->get(['ID', 'Title', 'Content']);

    return response()->json($clauses);
}
}
