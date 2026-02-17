<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryOffence;
use App\Models\HR\Discipline\DisciplinaryOffenceCategory;
use App\Models\HR\Discipline\DisciplinarySanction;
use Illuminate\Http\Request;

class DisciplinaryOffenceController extends Controller
{
    public function index()
    {
        $offences = DisciplinaryOffence::with(['category', 'recommendedSanction'])->orderBy('Name')->get();
        return view('hr.discipline.offences.index', compact('offences'));
    }

    public function create()
    {
        $categories = DisciplinaryOffenceCategory::orderBy('Name')->get();
        $sanctions = DisciplinarySanction::orderBy('Name')->get();
        return view('hr.discipline.offences.create', compact('categories', 'sanctions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'CategoryID' => ['nullable', 'integer', 'exists:t_HRDisciplinaryOffenceCategories,Id'],
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRDisciplinaryOffences,Code'],
            'Name' => ['required', 'string', 'max:150'],
            'Severity' => ['required', 'string', 'max:30'],
            'RecommendedSanctionID' => ['nullable', 'integer', 'exists:t_HRDisciplinarySanctions,Id'],
            'HearingRequired' => ['sometimes', 'boolean'],
            'SummaryDismissalAllowed' => ['sometimes', 'boolean'],
            'RequiresEvidence' => ['sometimes', 'boolean'],
            'RequiresApproval' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        DisciplinaryOffence::create([
            'CategoryID' => $data['CategoryID'] ?? null,
            'Code' => strtoupper($data['Code']),
            'Name' => $data['Name'],
            'Severity' => $data['Severity'],
            'RecommendedSanctionID' => $data['RecommendedSanctionID'] ?? null,
            'HearingRequired' => $request->boolean('HearingRequired', false),
            'SummaryDismissalAllowed' => $request->boolean('SummaryDismissalAllowed', false),
            'RequiresEvidence' => $request->boolean('RequiresEvidence', false),
            'RequiresApproval' => $request->boolean('RequiresApproval', false),
            'IsActive' => $request->boolean('IsActive', true),
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.offences.index')->with('success', 'Offence saved.');
    }

    public function edit($id)
    {
        $offence = DisciplinaryOffence::findOrFail($id);
        $categories = DisciplinaryOffenceCategory::orderBy('Name')->get();
        $sanctions = DisciplinarySanction::orderBy('Name')->get();
        return view('hr.discipline.offences.edit', compact('offence', 'categories', 'sanctions'));
    }

    public function update(Request $request, $id)
    {
        $offence = DisciplinaryOffence::findOrFail($id);
        $data = $request->validate([
            'CategoryID' => ['nullable', 'integer', 'exists:t_HRDisciplinaryOffenceCategories,Id'],
            'Code' => ['required', 'string', 'max:50', 'unique:t_HRDisciplinaryOffences,Code,'.$offence->Id.',Id'],
            'Name' => ['required', 'string', 'max:150'],
            'Severity' => ['required', 'string', 'max:30'],
            'RecommendedSanctionID' => ['nullable', 'integer', 'exists:t_HRDisciplinarySanctions,Id'],
            'HearingRequired' => ['sometimes', 'boolean'],
            'SummaryDismissalAllowed' => ['sometimes', 'boolean'],
            'RequiresEvidence' => ['sometimes', 'boolean'],
            'RequiresApproval' => ['sometimes', 'boolean'],
            'IsActive' => ['sometimes', 'boolean'],
        ]);

        $offence->update([
            'CategoryID' => $data['CategoryID'] ?? null,
            'Code' => strtoupper($data['Code']),
            'Name' => $data['Name'],
            'Severity' => $data['Severity'],
            'RecommendedSanctionID' => $data['RecommendedSanctionID'] ?? null,
            'HearingRequired' => $request->boolean('HearingRequired', false),
            'SummaryDismissalAllowed' => $request->boolean('SummaryDismissalAllowed', false),
            'RequiresEvidence' => $request->boolean('RequiresEvidence', false),
            'RequiresApproval' => $request->boolean('RequiresApproval', false),
            'IsActive' => $request->boolean('IsActive', true),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.offences.index')->with('success', 'Offence updated.');
    }
}
