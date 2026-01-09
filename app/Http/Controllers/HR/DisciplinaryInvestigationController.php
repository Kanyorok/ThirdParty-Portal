<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Discipline\DisciplinaryCaseStatusLog;
use App\Models\HR\Discipline\DisciplinaryInvestigation;
use App\Models\HR\Discipline\DisciplinaryInvestigationDocument;
use App\Models\HR\Employee;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DisciplinaryInvestigationController extends Controller
{
    public function edit($caseId)
    {
        $case = DisciplinaryCase::with('employee')->findOrFail($caseId);
        $investigation = DisciplinaryInvestigation::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $employees = Employee::where('Id', '!=', $case->EmployeeID)
            ->orderBy('FirstName')
            ->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);
        $documents = $investigation
            ? DisciplinaryInvestigationDocument::with('document')->where('InvestigationID', $investigation->Id)->get()
            : collect();

        return view('hr.discipline.cases.investigation', compact('case', 'investigation', 'employees', 'documents'));
    }

    public function store(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $data = $request->validate([
            'InvestigatorType' => ['required', 'string', 'max:30', Rule::in(['Internal', 'External'])],
            'InvestigatorID' => [
                'nullable',
                'exists:t_HREmployees,Id',
                Rule::requiredIf(fn () => $request->input('InvestigatorType') === 'Internal'),
                Rule::notIn([$case->EmployeeID]),
            ],
            'InvestigatorName' => [
                'nullable',
                'string',
                'max:150',
                Rule::requiredIf(fn () => $request->input('InvestigatorType') === 'External'),
            ],
            'StartDate' => ['nullable', 'date'],
            'EndDate' => ['nullable', 'date'],
            'ConflictDeclared' => ['sometimes', 'boolean'],
            'FindingsSummary' => ['nullable', 'string'],
            'Status' => ['nullable', 'string', 'max:30'],
            'ReportDocument' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
        ]);
        if ($data['InvestigatorType'] === 'External') {
            $data['InvestigatorID'] = null;
        } else {
            $data['InvestigatorName'] = null;
        }

        $reportDocumentId = null;
        if ($request->hasFile('ReportDocument')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::HRM),
                $request->file('ReportDocument'),
                auth()->user()
            )->document;
            $reportDocumentId = $document->Id;
        }

        $investigation = DisciplinaryInvestigation::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if ($investigation) {
            $investigation->update([
                'InvestigatorType' => $data['InvestigatorType'],
                'InvestigatorID' => $data['InvestigatorID'] ?? null,
                'InvestigatorName' => $data['InvestigatorName'] ?? null,
                'StartDate' => $data['StartDate'] ?? null,
                'EndDate' => $data['EndDate'] ?? null,
                'ConflictDeclared' => $request->boolean('ConflictDeclared', false),
                'FindingsSummary' => $data['FindingsSummary'] ?? null,
                'Status' => $data['Status'] ?? $investigation->Status ?? 'Draft',
                'ReportDocumentId' => $reportDocumentId ?? $investigation->ReportDocumentId,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        } else {
            $investigation = DisciplinaryInvestigation::create([
                'CaseID' => $case->Id,
                'InvestigatorType' => $data['InvestigatorType'],
                'InvestigatorID' => $data['InvestigatorID'] ?? null,
                'InvestigatorName' => $data['InvestigatorName'] ?? null,
                'StartDate' => $data['StartDate'] ?? null,
                'EndDate' => $data['EndDate'] ?? null,
                'ConflictDeclared' => $request->boolean('ConflictDeclared', false),
                'FindingsSummary' => $data['FindingsSummary'] ?? null,
                'Status' => $data['Status'] ?? 'Draft',
                'ReportDocumentId' => $reportDocumentId,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        $this->logStatus($case, 'Under Investigation', 'Investigation updated.');

        return redirect()->route('hr.discipline.cases.investigation.edit', $case->Id)->with('success', 'Investigation saved.');
    }

    public function approve(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $investigation = DisciplinaryInvestigation::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if (!$investigation) {
            return redirect()->route('hr.discipline.cases.investigation.edit', $case->Id)->withErrors([
                'status' => 'No investigation record found.',
            ]);
        }

        $investigation->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->logStatus($case, 'Investigation Approved', 'Investigation report approved.');

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Investigation approved.');
    }

    public function storeDocument(Request $request, $caseId)
    {
        $case = DisciplinaryCase::findOrFail($caseId);
        $investigation = DisciplinaryInvestigation::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        if (!$investigation) {
            return redirect()->route('hr.discipline.cases.investigation.edit', $case->Id)->withErrors([
                'status' => 'Create the investigation first.',
            ]);
        }

        $data = $request->validate([
            'Document' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
            'DocType' => ['nullable', 'string', 'max:50'],
            'Notes' => ['nullable', 'string'],
        ]);

        $document = DocumentService::createUpload(
            RepositoryService::module(ModulesEnum::HRM),
            $request->file('Document'),
            auth()->user()
        )->document;

        DisciplinaryInvestigationDocument::create([
            'InvestigationID' => $investigation->Id,
            'DocumentId' => $document->Id,
            'DocType' => $data['DocType'] ?? 'Evidence',
            'Notes' => $data['Notes'] ?? null,
            'UploadedBy' => auth()->id(),
            'UploadedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.cases.investigation.edit', $case->Id)->with('success', 'Document uploaded.');
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
