<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Exit\ExitLegalRef;
use Illuminate\Http\Request;

class ExitLegalRefController extends Controller
{
    public function index()
    {
        $refs = ExitLegalRef::orderBy('Section')->get();

        return view('hr.exit.config.legal_refs.index', compact('refs'));
    }

    public function create()
    {
        return view('hr.exit.config.legal_refs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:20', 'unique:t_HRExitLegalRefs,Code'],
            'Section' => ['required', 'string', 'max:50'],
            'Title' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        ExitLegalRef::create([
            'Code' => strtoupper($data['Code']),
            'Section' => $data['Section'],
            'Title' => $data['Title'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-legal-refs.index')->with('success', 'Legal reference saved.');
    }

    public function edit($id)
    {
        $ref = ExitLegalRef::findOrFail($id);

        return view('hr.exit.config.legal_refs.edit', compact('ref'));
    }

    public function update(Request $request, $id)
    {
        $ref = ExitLegalRef::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:20', 'unique:t_HRExitLegalRefs,Code,' . $ref->Id . ',Id'],
            'Section' => ['required', 'string', 'max:50'],
            'Title' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $ref->update([
            'Code' => strtoupper($data['Code']),
            'Section' => $data['Section'],
            'Title' => $data['Title'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.config.exit-legal-refs.index')->with('success', 'Legal reference updated.');
    }
}
