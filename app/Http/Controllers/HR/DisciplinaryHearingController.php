<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Discipline\DisciplinaryCaseStatusLog;
use App\Models\HR\Discipline\DisciplinaryHearing;
use App\Models\HR\Discipline\DisciplinaryHearingPanel;
use App\Models\HR\Discipline\DisciplinaryInvestigation;
use App\Models\HR\Employee;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisciplinaryHearingController extends Controller
{
    public function edit($caseId)
    {
        $case = DisciplinaryCase::with(['policy', 'employee'])->findOrFail($caseId);
        $hearings = DisciplinaryHearing::where('CaseID', $case->Id)
            ->with(['facilitator', 'minutes', 'panelMembers'])
            ->orderByDesc('CreatedOn')
            ->get();
        $hearing = $hearings->first();
        $employees = Employee::where('Id', '!=', $case->EmployeeID)
            ->orderBy('FirstName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);
        $panel = $hearing ? DisciplinaryHearingPanel::where('HearingID', $hearing->Id)->with('employee')->get() : collect();
        $investigation = DisciplinaryInvestigation::where('CaseID', $case->Id)
            ->latest('CreatedOn')
            ->with(['investigator', 'reportDocument'])
            ->first();

        return view('hr.discipline.cases.hearing', compact('case', 'hearing', 'hearings', 'employees', 'panel', 'investigation'));
    }

    public function store(Request $request, $caseId)
    {
        $case = DisciplinaryCase::with('policy')->findOrFail($caseId);
        if (! $case->policy?->AllowDirectHearing) {
            $hasApprovedInvestigation = $case->investigations()->where('Status', 'Approved')->exists();
            if (! $hasApprovedInvestigation) {
                return redirect()->route('hr.discipline.cases.hearing.edit', $case->Id)->withErrors([
                    'status' => 'Investigation approval is required before scheduling a hearing.',
                ]);
            }
        }

        $data = $request->validate([
            'HearingDate' => ['required', 'date'],
            'Venue' => ['nullable', 'string', 'max:150'],
            'HRFacilitatorID' => [
                'nullable',
                'exists:t_HREmployees,Id',
                Rule::notIn([$case->EmployeeID]),
            ],
            'EmployeeRepName' => ['nullable', 'string', 'max:150'],
            'Status' => ['nullable', 'string', 'max:30'],
            'CreateSubsequent' => ['sometimes', 'boolean'],
        ]);

        $hearing = DisciplinaryHearing::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $createSubsequent = $request->boolean('CreateSubsequent', false);
        if ($hearing && ! $createSubsequent) {
            $hearing->update([
                'HearingDate' => $data['HearingDate'],
                'Venue' => $data['Venue'] ?? null,
                'HRFacilitatorID' => $data['HRFacilitatorID'] ?? null,
                'EmployeeRepName' => $data['EmployeeRepName'] ?? null,
                'Status' => $data['Status'] ?? $hearing->Status ?? 'Scheduled',
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        } else {
            $hearing = DisciplinaryHearing::create([
                'CaseID' => $case->Id,
                'HearingDate' => $data['HearingDate'],
                'Venue' => $data['Venue'] ?? null,
                'HRFacilitatorID' => $data['HRFacilitatorID'] ?? null,
                'EmployeeRepName' => $data['EmployeeRepName'] ?? null,
                'Status' => $data['Status'] ?? 'Scheduled',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        $this->logStatus($case, 'Hearing Scheduled', 'Hearing scheduled.');

        return redirect()->route('hr.discipline.cases.hearing.edit', $case->Id)->with('success', 'Hearing saved.');
    }

    public function addPanel(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $hearing = DisciplinaryHearing::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if (! $hearing) {
            return redirect()->route('hr.discipline.cases.hearing.edit', $case->Id)->withErrors([
                'status' => 'Save hearing details first.',
            ]);
        }

        $data = $request->validate([
            'PanelMembers' => ['required', 'array'],
            'PanelMembers.*' => [
                'exists:t_HREmployees,Id',
                Rule::notIn([$case->EmployeeID]),
            ],
            'PanelRoles' => ['nullable', 'array'],
        ]);

        foreach ($data['PanelMembers'] as $index => $employeeId) {
            DisciplinaryHearingPanel::firstOrCreate([
                'HearingID' => $hearing->Id,
                'EmployeeID' => $employeeId,
            ], [
                'Role' => $data['PanelRoles'][$index] ?? null,
            ]);
        }

        return redirect()->route('hr.discipline.cases.hearing.edit', $case->Id)->with('success', 'Panel updated.');
    }

    public function storeMinutes(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $hearing = DisciplinaryHearing::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if (! $hearing) {
            return redirect()->route('hr.discipline.cases.hearing.edit', $case->Id)->withErrors([
                'status' => 'Save hearing details first.',
            ]);
        }

        $data = $request->validate([
            'MinutesDocument' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
            'PanelRecommendation' => ['nullable', 'string'],
        ]);

        $document = DocumentService::createUpload(
            RepositoryService::module(ModulesEnum::HRM),
            $request->file('MinutesDocument'),
            auth()->user()
        )->document;

        $hearing->update([
            'MinutesDocumentId' => $document->Id,
            'PanelRecommendation' => $data['PanelRecommendation'] ?? null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.cases.hearing.edit', $case->Id)->with('success', 'Minutes uploaded.');
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
