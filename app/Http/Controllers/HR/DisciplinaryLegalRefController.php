<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryLegalRef;
use Illuminate\Http\Request;

class DisciplinaryLegalRefController extends Controller
{
    public function index()
    {
        $refs = DisciplinaryLegalRef::orderBy('Section')->get();

        return view('hr.discipline.legal_refs.index', compact('refs'));
    }

    public function create()
    {
        return view('hr.discipline.legal_refs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:20', 'unique:t_HRDisciplinaryLegalRefs,Code'],
            'Section' => ['required', 'string', 'max:50'],
            'Title' => ['required', 'string', 'max:150'],
            'Description' => ['nullable', 'string'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        DisciplinaryLegalRef::create([
            'Code' => strtoupper($data['Code']),
            'Section' => $data['Section'],
            'Title' => $data['Title'],
            'Description' => $data['Description'] ?? null,
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.legal-refs.index')->with('success', 'Legal reference saved.');
    }

    public function edit($id)
    {
        $ref = DisciplinaryLegalRef::findOrFail($id);

        return view('hr.discipline.legal_refs.edit', compact('ref'));
    }

    public function update(Request $request, $id)
    {
        $ref = DisciplinaryLegalRef::findOrFail($id);
        $data = $request->validate([
            'Code' => ['required', 'string', 'max:20', 'unique:t_HRDisciplinaryLegalRefs,Code,'.$ref->Id.',Id'],
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

        return redirect()->route('hr.discipline.legal-refs.index')->with('success', 'Legal reference updated.');
    }
}
