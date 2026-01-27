<?php

namespace App\Http\Controllers\Legal;

use App\Enums\Core\PermissionEnum;
use App\Http\Controllers\Controller;
use App\Models\Legal\LegalCase;
use App\Models\Legal\LegalCaseOutcome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LegalCaseOutcomeController extends Controller
{
    public function index($caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCaseOutcome::class);

        $case = LegalCase::findOrFail($caseId);
        $outcomes = LegalCaseOutcome::with('case')->orderByDesc('JudgmentDate')->get();

        return view('legal.disputes.outcomes.index', compact('outcomes', 'case'));
    }

    public function create($caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCaseOutcome::class);

        $case = LegalCase::findOrFail($caseId);

        return view('legal.disputes.outcomes.create', compact('case'));
    }

    public function store(Request $request, $caseId)
    {
        $this->authorize(PermissionEnum::DisputeLitigationCreate, LegalCaseOutcome::class);

        // dd($request->all());
        $validated = $request->validate([
            'LegalCaseID' => 'required|exists:t_LegalCases,ID',
            'Outcome' => 'required|string|max:255',
            'JudgmentDate' => 'required|date|before_or_equal:today',
            'JudgeName' => 'required|string|max:255',
            'CourtDecision' => 'required|string',
            'PenaltyAmount' => 'nullable|numeric',
            'Remarks' => 'required|string',
        ]);

        $duplicate = LegalCaseOutcome::where('LegalCaseID', $caseId)
            ->where('JudgmentDate', $request->JudgmentDate)
            ->where('JudgeName', $request->JudgeName)
            ->exists();
        if ($duplicate) {
            return back()->with('error', 'Existing record for this Outcome');
        }

        try {
            DB::beginTransaction();

            $outcomes = LegalCaseOutcome::create([
                'LegalCaseID' => $caseId,
                'Outcome' => $validated['Outcome'],
                'JudgmentDate' => $validated['JudgmentDate'] ?? now(),
                'JudgeName' => $validated['JudgeName'],
                'CourtDecision' => $validated['CourtDecision'],
                'PenaltyAmount' => $validated['PenaltyAmount'] ?? 0.00,
                'Remarks' => $validated['Remarks'],
                'CreatedBy' => Auth::id(),
                'CreatedOn' => now(),
                'ModifiedBy' => Auth::id(),
                'ModifiedOn' => now(),
            ]);

            activity()
                ->performedOn(new LegalCaseOutcome())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Legal case outcome created');

            DB::commit();

            return redirect()->route('legal.cases.show', $caseId)->with('success', 'Case outcome recorded successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalCaseOutcome())
                ->causedBy(Auth::user())
                ->log('Error creating case outcome');

            Log::error('Error creating case outcome: ' . $th->getMessage());

            return back()->with('error', 'Error creating case outcome: ' . $th->getMessage());
        }
    }

    public function edit($caseId, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCaseOutcome::class);

        $outcome = LegalCaseOutcome::findOrFail($id);
        $case = LegalCase::findOrFail($outcome->LegalCaseID);

        return view('legal.disputes.outcomes.edit', compact('outcome', 'case'));
    }

    public function update(Request $request, $caseId, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationUpdate, LegalCaseOutcome::class);

        $outcome = LegalCaseOutcome::findOrFail($id);

        $data = $request->validate([
            'Outcome' => 'required|string|max:255',
            'JudgmentDate' => 'required|date',
            'JudgeName' => 'required|string|max:255',
            'CourtDecision' => 'required|string',
            'PenaltyAmount' => 'nullable|numeric',
            'Remarks' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $outcome->update([
                'Outcome' => $data['Outcome'],
                'JudgmentDate' => $data['JudgmentDate'] ?? now(),
                'JudgeName' => $data['JudgeName'],
                'CourtDecision' => $data['CourtDecision'],
                'PenaltyAmount' => $data['PenaltyAmount'] ?? 0.00,
                'Remarks' => $data['Remarks'],
                'ModifiedBy' => Auth::id(),
            ]);

            activity()
                ->performedOn(new LegalCaseOutcome())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Legal case outcome created');

            DB::commit();

            return redirect()->route('legal.cases.show', $caseId)->with('success', 'Case outcome updated successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalCaseOutcome())
                ->causedBy(Auth::user())
                ->log('Error updating case outcome');

            Log::error('Error updating case outcome: ' . $th->getMessage());

            return back()->with('error', 'Error updating case outcome: ' . $th->getMessage());
        }
    }

    public function show($caseId, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationView, LegalCaseOutcome::class);

        $outcome = LegalCaseOutcome::with('case')->findOrFail($id);
        $case = LegalCase::findOrFail($outcome->LegalCaseID);

        return view('legal.disputes.outcomes.show', compact('outcome', 'case'));
    }

    public function destroy($caseId, $id)
    {
        $this->authorize(PermissionEnum::DisputeLitigationDelete, LegalCaseOutcome::class);

        try {
            DB::beginTransaction();

            $outcome = LegalCaseOutcome::findOrFail($id);
            $outcome->DeletedBy = Auth::id();
            $outcome->save();
            $outcome->delete();

            activity()
                ->performedOn(new LegalCaseOutcome())
                ->causedBy(Auth::user())
                ->withProperties(['action' => 'create'])
                ->log('Legal case outcome deleted');

            DB::commit();

            return redirect()->route('legal.cases.show', $caseId)->with('success', 'Case outcome deleted successfully.');
        } catch (\Throwable $th) {
            DB::rollBack();

            activity()
                ->performedOn(new LegalCaseOutcome())
                ->causedBy(Auth::user())
                ->log('Error deleted case outcome');

            Log::error('Error deleted case outcome: ' . $th->getMessage());

            return back()->with('error', 'Error deleted case outcome: ' . $th->getMessage());
        }
    }
}
