<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryAppeal;
use App\Models\HR\Discipline\DisciplinaryAppealDocument;
use App\Models\HR\Discipline\DisciplinaryAppealPanel;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Discipline\DisciplinaryCaseStatusLog;
use App\Models\HR\Employee;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;

class DisciplinaryAppealController extends Controller
{
    public function edit($caseId)
    {
        $case = DisciplinaryCase::with('employee')->findOrFail($caseId);
        $appeal = DisciplinaryAppeal::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $employees = Employee::where('Id', '!=', $case->EmployeeID)
            ->orderBy('FirstName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);
        $panel = $appeal ? DisciplinaryAppealPanel::where('AppealID', $appeal->Id)->with('employee')->get() : collect();
        $documents = $appeal ? DisciplinaryAppealDocument::with('document')->where('AppealID', $appeal->Id)->get() : collect();

        return view('hr.discipline.cases.appeal', compact('case', 'appeal', 'employees', 'panel', 'documents'));
    }

    public function store(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $data = $request->validate([
            'AppealDate' => ['nullable', 'date'],
            'Grounds' => ['required', 'string'],
            'DeadlineDate' => ['nullable', 'date'],
            'HearingDate' => ['nullable', 'date'],
            'Status' => ['nullable', 'string', 'max:30'],
            'PanelMembers' => ['nullable', 'array'],
            'PanelMembers.*' => ['exists:t_HREmployees,Id', 'not_in:' . $case->EmployeeID],
            'PanelRoles' => ['nullable', 'array'],
            'AppealDocuments.*' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
        ]);

        $appeal = DisciplinaryAppeal::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if ($appeal) {
            $appeal->update([
                'AppealDate' => $data['AppealDate'] ?? $appeal->AppealDate ?? now()->toDateString(),
                'Grounds' => $data['Grounds'],
                'DeadlineDate' => $data['DeadlineDate'] ?? null,
                'HearingDate' => $data['HearingDate'] ?? null,
                'Status' => $data['Status'] ?? $appeal->Status ?? 'Submitted',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        } else {
            $appeal = DisciplinaryAppeal::create([
                'CaseID' => $case->Id,
                'AppealDate' => $data['AppealDate'] ?? now()->toDateString(),
                'Grounds' => $data['Grounds'],
                'DeadlineDate' => $data['DeadlineDate'] ?? null,
                'HearingDate' => $data['HearingDate'] ?? null,
                'Status' => $data['Status'] ?? 'Submitted',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        if (!empty($data['PanelMembers'])) {
            foreach ($data['PanelMembers'] as $index => $employeeId) {
                DisciplinaryAppealPanel::firstOrCreate([
                    'AppealID' => $appeal->Id,
                    'EmployeeID' => $employeeId,
                ], [
                    'Role' => $data['PanelRoles'][$index] ?? null,
                ]);
            }
        }

        if ($request->hasFile('AppealDocuments')) {
            foreach ($request->file('AppealDocuments') as $file) {
                $document = DocumentService::createUpload(
                    RepositoryService::module(ModulesEnum::HRM),
                    $file,
                    auth()->user()
                )->document;

                DisciplinaryAppealDocument::create([
                    'AppealID' => $appeal->Id,
                    'DocumentId' => $document->Id,
                    'DocType' => 'Appeal',
                    'UploadedBy' => auth()->id(),
                    'UploadedOn' => now(),
                ]);
            }
        }

        $this->logStatus($case, 'Appealed', 'Appeal submitted.');

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Appeal saved.');
    }

    public function decide(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $appeal = DisciplinaryAppeal::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if (!$appeal) {
            return redirect()->route('hr.discipline.cases.appeal.edit', $case->Id)->withErrors([
                'status' => 'No appeal record found.',
            ]);
        }

        $data = $request->validate([
            'Outcome' => ['required', 'string', 'max:30'],
            'DecisionSummary' => ['nullable', 'string'],
        ]);

        $appeal->update([
            'Outcome' => $data['Outcome'],
            'DecisionSummary' => $data['DecisionSummary'] ?? null,
            'Status' => 'Decided',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->logStatus($case, 'Appeal Decided', 'Appeal decision recorded.');

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Appeal decision saved.');
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

        if ($case->Status !== $toStatus) {
            $case->update([
                'Status' => $toStatus,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }
    }
}
