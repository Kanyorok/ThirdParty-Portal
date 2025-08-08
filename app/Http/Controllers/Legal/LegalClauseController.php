<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Legal\LegalClause;
use Illuminate\Support\Facades\Auth;

class LegalClauseController extends Controller
{
    public function index()
    {
        $clauses = LegalClause::where('IsActive', 1)->orderByDesc('CreatedOn')->get();
        return view('legal.clauses.index', compact('clauses'));
    }

    public function create()
    {
        return view('legal.clauses.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Title' => 'required|string|max:255',
            'ClauseType' => 'nullable|string|max:100',
            'Content' => 'required|string',
            'Version' => 'nullable|string|max:50',
        ]);

        LegalClause::create([
            'Title' => $request->Title,
            'ClauseType' => $request->ClauseType,
            'Content' => $request->Content,
            'IsStandard' => $request->has('IsStandard') ? 1 : 0,
            'Version' => $request->Version,
            'IsActive' => 1,
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('legal.clauses.index')->with('success', 'Clause created successfully.');
    }

    public function edit($id)
    {
        $clause = LegalClause::findOrFail($id);
        return view('legal.clauses.edit', compact('clause'));
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
            'IsStandard' => $request->has('IsStandard') ? 1 : 0,
            'Version' => $request->Version,
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.clauses.index')->with('success', 'Clause updated successfully.');
    }

    public function destroy($id)
    {
        $clause = LegalClause::findOrFail($id);
        $clause->IsActive = 0;
        $clause->ModifiedBy = Auth::id();
        $clause->ModifiedOn = now();
        $clause->save();

        return redirect()->route('legal.clauses.index')->with('success', 'Clause archived.');
    }
    public function show($id)
{
    $clause = LegalClause::findOrFail($id);
    return view('legal.clauses.show', compact('clause'));
}
}
