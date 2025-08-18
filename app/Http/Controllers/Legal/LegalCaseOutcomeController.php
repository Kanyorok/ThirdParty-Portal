<?php

namespace App\Http\Controllers\Legal;

use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LegalCaseOutcomeController extends Controller
{
    public function index($caseId)
    {
        $case = LegalCase::findOrFail($caseId);
        $outcomes = LegalCaseOutcome::with('case')->orderByDesc('JudgmentDate')->get();
        return view('legal.disputes.outcomes.index', compact('outcomes','case'));
    }

    public function create($caseId)
    {
        $case = LegalCase::findOrFail($caseId);

        return view('legal.disputes.outcomes.create', compact('case'));
    }

    public function store(Request $request, $caseId)
    {
        // dd($request->all());
        $validated = $request->validate([
            'LegalCaseID' => 'required|exists:t_LegalCases,ID',
            'Outcome' => 'required|string|max:255',
            'JudgmentDate' => 'required|date|before_or_equal:today',
            'JudgeName' => 'required|string|max:255',
            'CourtDecision' => 'required|string',
            'PenaltyAmount' => 'required|numeric',
            'Remarks' => 'required|string',
        ]);

        $outcomes = LegalCaseOutcome::create([
            'LegalCaseID' => $caseId,
            'Outcome' => $validated['Outcome'],
            'JudgmentDate' => $validated['JudgmentDate'] ?? now(),
            'JudgeName' => $validated['JudgeName'],
            'CourtDecision' => $validated['CourtDecision'],
            'PenaltyAmount' => $validated['PenaltyAmount'],
            'Remarks' => $validated['Remarks'],
            'CreatedBy' => Auth::id(),
            'CreatedOn' => now(),
            'ModifiedBy' => Auth::id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('legal.cases.show', $caseId)->with('success', 'Case outcome recorded successfully.');
    }

    public function edit($caseId, $id)
    {
        $outcome = LegalCaseOutcome::findOrFail($id);
        $case = LegalCase::findOrFail($outcome->LegalCaseID);

        return view('legal.disputes.outcomes.edit', compact('outcome', 'case'));
    }

    public function update(Request $request, $caseId, $id)
    {
        $outcome = LegalCaseOutcome::findOrFail($id);

        $data = $request->validate([
            'Outcome' => 'required|string|max:255',
            'JudgmentDate' => 'required|date',
            'JudgeName' => 'required|string|max:255',
            'CourtDecision' => 'required|string',
            'PenaltyAmount' => 'required|numeric',
            'Remarks' => 'required|string',
        ]);

        $data['ModifiedBy'] = Auth::id();
        $data['ModifiedOn'] = now();

        $outcome->update($data);

        return redirect()->route('legal.cases.show', $caseId)->with('success', 'Case outcome updated successfully.');
    }

    public function show($caseId, $id)
    {
        $outcome = LegalCaseOutcome::with('case')->findOrFail($id);
        $case = LegalCase::findOrFail($outcome->LegalCaseID);
        return view('legal.disputes.outcomes.show', compact('outcome', 'case'));
    }

    public function destroy($caseId, $id)
    {
        $outcome = LegalCaseOutcome::findOrFail($id);
        $outcome->DeletedBy = Auth::id();
        $outcome->save();
        $outcome->delete();

        return redirect()->route('legal.cases.show', $caseId)->with('success', 'Case outcome deleted successfully.');
    }
}
