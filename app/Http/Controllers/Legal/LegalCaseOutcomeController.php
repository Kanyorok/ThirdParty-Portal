<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\Disputes\LegalCaseOutcome;
use App\Models\Legal\LegalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCaseOutcomeController extends Controller
{
    public function index()
    {
        $outcomes = LegalCaseOutcome::with('case')->orderByDesc('JudgmentDate')->get();
        return view('legal.disputes.outcomes.index', compact('outcomes'));
    }

    public function create(Request $request)
    {
        $caseId = $request->get('case_id');
        $case = LegalCase::findOrFail($caseId);

        return view('legal.disputes.outcomes.create', compact('case'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'LegalCaseID' => 'required|exists:t_LegalCases,ID',
            'Outcome' => 'required|string|max:255',
            'JudgmentDate' => 'nullable|date',
            'JudgeName' => 'nullable|string|max:255',
            'CourtDecision' => 'nullable|string',
            'PenaltyAmount' => 'nullable|numeric',
            'Remarks' => 'nullable|string',
        ]);

        $data['CreatedBy'] = Auth::id();
        $data['CreatedOn'] = now();

        LegalCaseOutcome::create($data);

        return redirect()->route('legal.cases.show', $data['LegalCaseID'])->with('success', 'Case outcome recorded successfully.');
    }

    public function edit($id)
    {
        $outcome = LegalCaseOutcome::findOrFail($id);
        $case = LegalCase::findOrFail($outcome->LegalCaseID);

        return view('legal.disputes.outcomes.edit', compact('outcome', 'case'));
    }

    public function update(Request $request, $id)
    {
        $outcome = LegalCaseOutcome::findOrFail($id);

        $data = $request->validate([
            'Outcome' => 'required|string|max:255',
            'JudgmentDate' => 'nullable|date',
            'JudgeName' => 'nullable|string|max:255',
            'CourtDecision' => 'nullable|string',
            'PenaltyAmount' => 'nullable|numeric',
            'Remarks' => 'nullable|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $outcome->update($data);

        return redirect()->route('legal.cases.show', $outcome->LegalCaseID)->with('success', 'Case outcome updated successfully.');
    }

    public function show($id)
    {
        $outcome = LegalCaseOutcome::with('case')->findOrFail($id);
        return view('legal.disputes.outcomes.show', compact('outcome'));
    }
}
