<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Discipline\DisciplinaryCaseDocument;
use App\Models\HR\Discipline\DisciplinaryCaseStatusLog;
use App\Models\HR\Discipline\DisciplinaryNotice;
use App\Models\HR\Discipline\DisciplinaryOffence;
use App\Models\HR\Discipline\DisciplinaryPolicy;
use App\Models\HR\Discipline\DisciplinaryResponse;
use App\Models\HR\Employee;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DisciplinaryCaseController extends Controller
{
    public function index(Request $request)
    {
        $query = DisciplinaryCase::with(['employee', 'offence', 'policy'])
            ->orderByDesc('CreatedOn');

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        if ($request->filled('employee')) {
            $query->where('EmployeeID', $request->employee);
        }

        $cases = $query->paginate(30);
        $employees = Employee::orderBy('FirstName')->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);

        return view('hr.discipline.cases.index', compact('cases', 'employees'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id', 'FirstName', 'LastName', 'EmployeeNo', 'EmploymentType', 'ContractType']);
        $offences = DisciplinaryOffence::where('IsActive', 1)->orderBy('Name')->get();
        $policies = DisciplinaryPolicy::where('IsActive', 1)->orderByDesc('EffectiveFrom')->get();

        return view('hr.discipline.cases.create', compact('employees', 'offences', 'policies'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required', 'exists:t_HREmployees,Id'],
            'ComplainantType' => ['nullable', 'string', 'max:30'],
            'ComplainantID' => ['nullable', 'exists:t_HREmployees,Id'],
            'ComplainantName' => ['nullable', 'string', 'max:150'],
            'OffenceID' => ['required', 'exists:t_HRDisciplinaryOffences,Id'],
            'PolicyID' => ['nullable', 'exists:t_HRDisciplinaryPolicies,Id'],
            'IncidentDate' => ['nullable', 'date'],
            'ReportedDate' => ['nullable', 'date'],
            'Description' => ['nullable', 'string'],
            'EvidenceFiles.*' => ['nullable', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
        ]);

        if (! empty($data['ComplainantID']) && (int)$data['ComplainantID'] === (int)$data['EmployeeID']) {
            return back()->withErrors([
                'ComplainantID' => 'Complainant cannot be the same as the employee.',
            ])->withInput();
        }

        $offence = DisciplinaryOffence::find($data['OffenceID']);
        if ($offence?->RequiresEvidence && ! $request->hasFile('EvidenceFiles')) {
            return back()->withErrors(['EvidenceFiles' => 'Evidence is required for this offence.'])->withInput();
        }

        $case = DisciplinaryCase::create([
            'CaseNo' => $this->generateCaseNo(),
            'EmployeeID' => $data['EmployeeID'],
            'ComplainantType' => $data['ComplainantType'] ?? null,
            'ComplainantID' => $data['ComplainantID'] ?? null,
            'ComplainantName' => $data['ComplainantName'] ?? null,
            'OffenceID' => $data['OffenceID'],
            'PolicyID' => $data['PolicyID'] ?? null,
            'Severity' => $offence?->Severity,
            'IncidentDate' => $data['IncidentDate'] ?? null,
            'ReportedDate' => $data['ReportedDate'] ?? null,
            'Description' => $data['Description'] ?? null,
            'Status' => 'Reported',
            'HearingRequired' => $offence?->HearingRequired ?? false,
            'SummaryDismissalAllowed' => $offence?->SummaryDismissalAllowed ?? false,
            'EvidenceRequired' => $offence?->RequiresEvidence ?? false,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        $this->logStatus($case, 'Reported', 'Case created.');

        if ($request->hasFile('EvidenceFiles')) {
            foreach ($request->file('EvidenceFiles') as $file) {
                $document = DocumentService::createUpload(
                    RepositoryService::module(ModulesEnum::HRM),
                    $file,
                    auth()->user()
                )->document;

                DisciplinaryCaseDocument::create([
                    'CaseID' => $case->Id,
                    'DocumentId' => $document->Id,
                    'DocType' => 'Evidence',
                    'Title' => $file->getClientOriginalName(),
                    'UploadedBy' => auth()->id(),
                    'UploadedOn' => now(),
                ]);
            }
        }

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Case created.');
    }

    public function show($id)
    {
        $case = DisciplinaryCase::with([
            'employee',
            'complainant',
            'offence',
            'policy',
            'documents.document',
            'statusLogs',
        ])->findOrFail($id);

        $notice = DisciplinaryNotice::where('CaseID', $case->Id)->latest('CreatedOn')->first();
        $response = DisciplinaryResponse::where('CaseID', $case->Id)->latest('SubmittedOn')->first();

        return view('hr.discipline.cases.show', compact('case', 'notice', 'response'));
    }

    public function storeDocument(Request $request, $id)
    {
        $case = DisciplinaryCase::findOrFail($id);
        $data = $request->validate([
            'Document' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
            'Title' => ['nullable', 'string', 'max:150'],
            'DocType' => ['nullable', 'string', 'max:50'],
            'Notes' => ['nullable', 'string'],
        ]);

        $document = DocumentService::createUpload(
            RepositoryService::module(ModulesEnum::HRM),
            $request->file('Document'),
            auth()->user()
        )->document;

        DisciplinaryCaseDocument::create([
            'CaseID' => $case->Id,
            'DocumentId' => $document->Id,
            'DocType' => $data['DocType'] ?? 'Evidence',
            'Title' => $data['Title'] ?? $request->file('Document')->getClientOriginalName(),
            'Notes' => $data['Notes'] ?? null,
            'UploadedBy' => auth()->id(),
            'UploadedOn' => now(),
        ]);

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Document uploaded.');
    }

    public function close(Request $request, $id)
    {
        $case = DisciplinaryCase::with('policy')->findOrFail($id);
        if ($case->Status === 'Closed') {
            return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Case already closed.');
        }

        $retentionMonths = $case->policy?->RetentionMonths ?? 24;
        $case->update([
            'ClosedOn' => now(),
            'ClosedBy' => auth()->id(),
            'RetentionUntil' => now()->addMonths($retentionMonths)->toDateString(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->logStatus($case, 'Closed', $request->input('Remarks'));

        return redirect()->route('hr.discipline.cases.show', $case->Id)->with('success', 'Case closed.');
    }

    private function generateCaseNo(): string
    {
        return 'DISC-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
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
