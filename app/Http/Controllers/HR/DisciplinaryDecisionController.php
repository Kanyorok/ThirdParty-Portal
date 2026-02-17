<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Discipline\DisciplinaryCaseStatusLog;
use App\Models\HR\Discipline\DisciplinaryDecision;
use App\Models\HR\Discipline\DisciplinaryHearing;
use App\Models\HR\Discipline\DisciplinaryLegalRef;
use App\Models\HR\Discipline\DisciplinaryNotice;
use App\Models\HR\Discipline\DisciplinaryResponse;
use App\Models\HR\Discipline\DisciplinarySanction;
use App\Models\HR\MonthlyDeduction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DisciplinaryDecisionController extends Controller
{
    public function edit($caseId)
    {
        $case = DisciplinaryCase::with(['employee', 'policy'])->findOrFail($caseId);
        $decision = DisciplinaryDecision::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $sanctions = DisciplinarySanction::where('IsActive', 1)->orderBy('Name')->get();
        $legalRefs = DisciplinaryLegalRef::where('IsActive', 1)->orderBy('Section')->get();
        $investigation = $case->investigations()->latest('CreatedOn')->first();
        $hearing = DisciplinaryHearing::where('CaseID', $case->Id)->latest('CreatedOn')->first();

        return view('hr.discipline.cases.decision', compact('case', 'decision', 'sanctions', 'legalRefs', 'investigation', 'hearing'));
    }

    public function store(Request $request, $caseId)
    {
        $case = DisciplinaryCase::with('employee')->findOrFail($caseId);
        $data = $request->validate([
            'DecisionDate' => ['required', 'date'],
            'DecisionSummary' => ['required', 'string'],
            'PolicyClause' => ['nullable', 'string', 'max:150'],
            'LegalRefID' => ['nullable', 'exists:t_HRDisciplinaryLegalRefs,Id'],
            'SanctionID' => ['required', 'exists:t_HRDisciplinarySanctions,Id'],
            'SanctionStartDate' => ['nullable', 'date'],
            'SanctionEndDate' => ['nullable', 'date'],
            'PayrollImpact' => ['sometimes', 'boolean'],
            'PayrollImpactAmount' => ['nullable', 'numeric', 'min:0'],
            'Status' => ['nullable', 'string', 'max:30'],
        ]);

        $sanction = DisciplinarySanction::find($data['SanctionID']);
        if ($case->EvidenceRequired && $case->documents()->count() === 0) {
            return back()->withErrors(['Evidence' => 'Evidence is required before applying a sanction.'])->withInput();
        }

        $notice = DisciplinaryNotice::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $response = $notice
            ? DisciplinaryResponse::where('NoticeID', $notice->Id)->latest('SubmittedOn')->first()
            : DisciplinaryResponse::where('CaseID', $case->Id)->latest('SubmittedOn')->first();
        if (!$response && $notice?->ResponseDueOn) {
            $deadline = Carbon::parse($notice->ResponseDueOn)->endOfDay();
            if (now()->lessThan($deadline)) {
                return back()->withErrors(['Response' => 'Response deadline has not passed.'])->withInput();
            }
        }

        $hearing = DisciplinaryHearing::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if (($case->HearingRequired || $sanction?->IsSuspension || $sanction?->UpdatesEmploymentStatus) && !$hearing) {
            return back()->withErrors(['Hearing' => 'Hearing must be scheduled before this decision.'])->withInput();
        }
        if (($sanction?->IsSuspension || $sanction?->UpdatesEmploymentStatus) && !$hearing?->MinutesDocumentId) {
            return back()->withErrors(['Hearing' => 'Hearing minutes are required for suspension/termination.'])->withInput();
        }

        $decision = DisciplinaryDecision::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if ($decision) {
            $decision->update([
                'DecisionDate' => $data['DecisionDate'],
                'DecisionSummary' => $data['DecisionSummary'],
                'PolicyClause' => $data['PolicyClause'] ?? null,
                'LegalRefID' => $data['LegalRefID'] ?? null,
                'InvestigationID' => $case->investigations()->latest('CreatedOn')->value('Id'),
                'HearingID' => $hearing?->Id,
                'SanctionID' => $data['SanctionID'],
                'SanctionStartDate' => $data['SanctionStartDate'] ?? null,
                'SanctionEndDate' => $data['SanctionEndDate'] ?? null,
                'PayrollImpact' => $request->boolean('PayrollImpact', false),
                'Status' => $data['Status'] ?? 'Approved',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        } else {
            $decision = DisciplinaryDecision::create([
                'CaseID' => $case->Id,
                'DecisionDate' => $data['DecisionDate'],
                'DecisionSummary' => $data['DecisionSummary'],
                'PolicyClause' => $data['PolicyClause'] ?? null,
                'LegalRefID' => $data['LegalRefID'] ?? null,
                'InvestigationID' => $case->investigations()->latest('CreatedOn')->value('Id'),
                'HearingID' => $hearing?->Id,
                'SanctionID' => $data['SanctionID'],
                'SanctionStartDate' => $data['SanctionStartDate'] ?? null,
                'SanctionEndDate' => $data['SanctionEndDate'] ?? null,
                'PayrollImpact' => $request->boolean('PayrollImpact', false),
                'Status' => $data['Status'] ?? 'Approved',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        if ($decision->PayrollImpact && $data['PayrollImpactAmount'] !== null) {
            $date = $data['SanctionStartDate'] ?? $data['DecisionDate'];
            $month = (int)Carbon::parse($date)->month;
            $year = (int)Carbon::parse($date)->year;

            MonthlyDeduction::create([
                'EmployeeID' => $case->EmployeeID,
                'DeductionID' => null,
                'Name' => $sanction?->Name ?? 'Disciplinary Deduction',
                'Amount' => $data['PayrollImpactAmount'],
                'Month' => $month,
                'Year' => $year,
                'IsRecurring' => false,
                'IsAutoCalculated' => false,
                'Status' => 'Pending',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        if ($sanction?->UpdatesEmploymentStatus && $sanction->EmploymentStatus) {
            $case->employee?->update([
                'Status' => $sanction->EmploymentStatus,
                'StatusChangedOn' => now(),
                'StatusChangedBy' => auth()->id(),
            ]);
        }

        $newStatus = $decision->Status === 'Approved' ? 'Sanction Applied' : 'Decision Pending';
        $case->update([
            'Outcome' => $sanction?->Name,
            'Status' => $newStatus,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->logStatus($case, $newStatus, 'Decision recorded.');

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Decision saved.');
    }

    private function logStatus(DisciplinaryCase $case, string $toStatus, ?string $remarks = null): void
    {
        DisciplinaryCaseStatusLog::create([
            'CaseID' => $case->Id,
            'FromStatus' => $case->Status,
            'ToStatus' => $toStatus,
            'Remarks' => $remarks,
            'ChangedBy' => auth()->id(),
            'ChangedOn' => now(),
        ]);
    }
}
