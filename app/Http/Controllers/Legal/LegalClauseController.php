<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Core\CodeDetail;
use Illuminate\Http\Request;
use App\Models\Legal\LegalClause;
use Illuminate\Support\Facades\Auth;

class LegalClauseController extends Controller
{
    public function index()
    {
        $clauses = LegalClause::all();
        return view('legal.clauses.index', compact('clauses'));
    }

    public function create()
    {
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'ClauseTypes')
            ->get();
        return view('legal.clauses.create', compact('details'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'Title' => 'required|string|max:255',
            'ClauseType' => 'required|string|max:100',
            'Content' => 'required|string',
            'Version' => 'required|string|max:50',
        ]);

        $clauses = LegalClause::create([
            'Title' => $validated['Title'],
            'ClauseType' => $validated['ClauseType'],
            'Content' => $validated['Content'],
            'Version' => $validated['Version'],
            'IsStandard' => $validated['IsStandard']?? 'No',
            'ClauseDMSDocID' => $request->ClauseDMSDocID ?? null,
            'CreatedBy' => Auth::id(),
            'ModifiedBy' => Auth::Id(),
        ]);

        return redirect()->route('legal.clauses.index')->with('success', 'Clause created successfully.');
    }

    public function edit($id)
    {
        $clause = LegalClause::findOrFail($id);
        $details = CodeDetail::select('Value')
            ->where('CodeID', 'ClauseTypes')
            ->get();
        return view('legal.clauses.edit', compact('clause', 'details'));
    }

    public function update(Request $request, $id)
    {
        $clause = LegalClause::findOrFail($id);

        $request->validate([
            'Title' => 'required|string|max:255',
            'ClauseType' => 'nullable|string|max:100',
            'Content' => 'required|string',
            'Version' => 'nullable|string|max:50',
        ]);

        $clause->update([
            'Title' => $request->Title,
            'ClauseType' => $request->ClauseType,
            'Content' => $request->Content,
            'IsStandard' => $request->has('IsStandard') ? 'Yes' : 'No',
            'Version' => $request->Version,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.clauses.index')->with('success', 'Clause updated successfully.');
    }

    public function destroy($id)
    {
        $clause = LegalClause::findOrFail($id);
        $clause->DeletedBy = Auth::id();
        $clause->save();
        $clause->delete();

        return redirect()->route('legal.clauses.index')->with('success', 'Clause archived.');
    }
    public function show($id)
{
    $clause = LegalClause::findOrFail($id);
    return view('legal.clauses.show', compact('clause'));
}
}
