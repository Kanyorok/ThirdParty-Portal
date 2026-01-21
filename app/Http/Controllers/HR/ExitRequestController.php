<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\HR\Discipline\DisciplinaryCase;
use App\Models\HR\Employee;
use App\Models\HR\MonthlyAllowance;
use App\Models\HR\MonthlyDeduction;
use App\Models\HR\Exit\ExitChecklistItem;
use App\Models\HR\Exit\ExitClearance;
use App\Models\HR\Exit\ExitClearanceDepartment;
use App\Models\HR\Exit\ExitDocument;
use App\Models\HR\Exit\ExitNotice;
use App\Models\HR\Exit\ExitPolicy;
use App\Models\HR\Exit\ExitRedundancy;
use App\Models\HR\Exit\ExitRequest;
use App\Models\HR\Exit\ExitStatusLog;
use App\Models\HR\Exit\ExitTerminalDue;
use App\Models\HR\Exit\ExitType;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use App\Services\HR\ExitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExitRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ExitRequest::with(['employee', 'exitType', 'policy'])
            ->orderByDesc('CreatedOn');

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        if ($request->filled('exitType')) {
            $query->where('ExitTypeID', $request->exitType);
        }

        if ($request->filled('employee')) {
            $query->where('EmployeeID', $request->employee);
        }

        $requests = $query->paginate(30);
        $employees = Employee::orderBy('FirstName')->get(['Id', 'FirstName', 'LastName', 'EmployeeNo']);
        $exitTypes = ExitType::where('IsActive', 1)->orderBy('Name')->get(['Id', 'Name']);

        return view('hr.exit.requests.index', compact('requests', 'employees', 'exitTypes'));
    }

    public function create()
    {
        $activeExitEmployeeIds = ExitRequest::whereNotIn('Status', ['Closed', 'Rejected'])
            ->whereNull('DeletedOn')
            ->pluck('EmployeeID')
            ->unique();

        $employeesQuery = Employee::whereNull('DeletedOn')
            ->where('IsActive', 1)
            ->where(function ($q) {
                $q->whereNull('Status')->orWhere('Status', '!=', 'Exited');
            })
            ->orderBy('FirstName');
        if ($activeExitEmployeeIds->isNotEmpty()) {
            $employeesQuery->whereNotIn('Id', $activeExitEmployeeIds);
        }
        $employees = $employeesQuery->get(['Id', 'FirstName', 'LastName', 'EmployeeNo', 'EmploymentType', 'ContractType']);
        $exitTypes = ExitType::where('IsActive', 1)->orderBy('Name')->get();
        $policies = ExitPolicy::where('IsActive', 1)->orderByDesc('EffectiveFrom')->get();
        $cases = DisciplinaryCase::orderByDesc('CreatedOn')->get(['Id', 'CaseNo', 'EmployeeID', 'Status']);
        $redundancies = ExitRedundancy::orderByDesc('CreatedOn')->get(['Id', 'RefNo', 'Status']);

        return view('hr.exit.requests.create', compact('employees', 'exitTypes', 'policies', 'cases', 'redundancies'));
    }

    public function store(Request $request, ExitService $exitService)
    {
        $data = $request->validate([
            'EmployeeID' => ['required', 'exists:t_HREmployees,Id'],
            'ExitTypeID' => ['required', 'exists:t_HRExitTypes,Id'],
            'PolicyID' => ['nullable', 'exists:t_HRExitPolicies,Id'],
            'CaseID' => ['nullable', 'exists:t_HRDisciplinaryCases,Id'],
            'RedundancyID' => ['nullable', 'exists:t_HRExitRedundancies,Id'],
            'InitiatorType' => ['required', 'string', 'max:30'],
            'RequestedOn' => ['nullable', 'date'],
            'NoticeDate' => ['nullable', 'date'],
            'ProposedLastDay' => ['nullable', 'date'],
            'EffectiveExitDate' => ['nullable', 'date'],
            'Reason' => ['nullable', 'string'],
            'NoticePayInLieu' => ['sometimes', 'boolean'],
            'NoticeWaived' => ['sometimes', 'boolean'],
            'Action' => ['nullable', 'string'],
        ]);

        $employee = Employee::find($data['EmployeeID']);
        $exitType = ExitType::find($data['ExitTypeID']);
        $policy = !empty($data['PolicyID']) ? ExitPolicy::find($data['PolicyID']) : null;

        $activeExitExists = ExitRequest::where('EmployeeID', $data['EmployeeID'])
            ->whereNotIn('Status', ['Closed', 'Rejected'])
            ->whereNull('DeletedOn')
            ->exists();

        if ($activeExitExists) {
            return back()->withErrors(['EmployeeID' => 'This employee already has an active exit request.'])->withInput();
        }

        if ($exitType?->RequiresCase && empty($data['CaseID'])) {
            return back()->withErrors(['CaseID' => 'This exit type requires a disciplinary case.'])->withInput();
        }

        if ($exitType?->IsRedundancy && empty($data['RedundancyID'])) {
            return back()->withErrors(['RedundancyID' => 'Redundancy exit requires a redundancy record.'])->withInput();
        }

        if ($exitType?->IsEmployerInitiated && empty($data['CaseID']) && empty($data['RedundancyID'])) {
            return back()->withErrors(['ExitTypeID' => 'Employer-initiated exits must reference a case or redundancy.'])->withInput();
        }

        $noticeInfo = $exitService->resolveNoticePeriod($policy, $employee);
        $noticeDays = $noticeInfo['days'];
        $payInLieuAllowed = $noticeInfo['payInLieuAllowed'];

        $noticePayInLieu = $request->boolean('NoticePayInLieu');
        if ($noticePayInLieu && !$payInLieuAllowed) {
            return back()->withErrors(['NoticePayInLieu' => 'Notice pay in lieu is not allowed for this policy.'])->withInput();
        }

        $noticeDate = $data['NoticeDate'] ?? $data['RequestedOn'] ?? now()->toDateString();
        $effectiveExitDate = $data['EffectiveExitDate'] ?? $data['ProposedLastDay'];
        if (!$effectiveExitDate && $noticeDate) {
            $effectiveExitDate = Carbon::parse($noticeDate)->addDays($noticeDays)->toDateString();
        }

        $noticePayAmount = $noticePayInLieu ? $exitService->calculateNoticePay($employee, $noticeDays) : null;
        $action = strtolower($data['Action'] ?? 'draft');
        $status = $action === 'submit' ? 'Submitted' : 'Draft';

        $exit = ExitRequest::create([
            'ExitNo' => $this->generateExitNo(),
            'EmployeeID' => $employee->Id,
            'ExitTypeID' => $exitType->Id,
            'PolicyID' => $policy?->Id,
            'CaseID' => $data['CaseID'] ?? null,
            'RedundancyID' => $data['RedundancyID'] ?? null,
            'InitiatorType' => $data['InitiatorType'] ?? 'Employee',
            'InitiatedBy' => auth()->id(),
            'InitiatedOn' => now(),
            'RequestedOn' => $data['RequestedOn'] ?? now()->toDateString(),
            'Reason' => $data['Reason'] ?? null,
            'NoticeDate' => $noticeDate,
            'ProposedLastDay' => $data['ProposedLastDay'] ?? null,
            'EffectiveExitDate' => $effectiveExitDate,
            'NoticeDays' => $noticeDays,
            'NoticePayInLieu' => $noticePayInLieu,
            'NoticePayAmount' => $noticePayAmount,
            'NoticeWaived' => $request->boolean('NoticeWaived', false),
            'ApprovalStatus' => 'Pending',
            'Status' => $status,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        $this->logStatus($exit, $status, $status === 'Submitted' ? 'Exit request submitted.' : 'Exit request drafted.');

        return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Exit request saved.');
    }

    public function show($id)
    {
        $exit = ExitRequest::with([
            'employee',
            'exitType',
            'policy',
            'case',
            'redundancy',
            'notices.document',
            'documents.document',
            'statusLogs',
            'clearances.department',
            'terminalDues',
            'interviews',
        ])->findOrFail($id);

        return view('hr.exit.requests.show', compact('exit'));
    }

    public function clearances($id)
    {
        $exit = ExitRequest::with(['employee', 'clearances.department', 'clearances.clearedByUser', 'policy', 'exitType'])
            ->findOrFail($id);

        if ($exit->Status !== 'Draft' && $exit->clearances()->count() === 0) {
            $this->syncClearances($exit);
            $exit->load(['clearances.department']);
        }

        $checklistItems = collect();
        if ($exit->policy?->ChecklistTemplateID) {
            $checklistItems = ExitChecklistItem::with('clearanceDepartment')
                ->where('TemplateID', $exit->policy->ChecklistTemplateID)
                ->whereNull('DeletedOn')
                ->orderBy('Sequence')
                ->get()
                ->groupBy(function ($item) {
                    return $item->clearanceDepartment?->Name ?? 'General';
                });
        }

        return view('hr.exit.requests.clearances', compact('exit', 'checklistItems'));
    }

    public function submit($id)
    {
        $exit = ExitRequest::findOrFail($id);
        if ($exit->Status === 'Submitted') {
            return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Exit request already submitted.');
        }

        $this->logStatus($exit, 'Submitted', 'Exit request submitted.');
        $this->syncClearances($exit);

        return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Exit request submitted.');
    }

    public function approve(Request $request, $id)
    {
        $exit = ExitRequest::with('policy')->findOrFail($id);
        if ($exit->ApprovalStatus === 'Approved') {
            return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Exit request already approved.');
        }

        $exit->update([
            'ApprovalStatus' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'Status' => 'Approved',
            'FinalPayrollStatus' => $exit->FinalPayrollStatus ?? 'Pending',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->syncClearances($exit);
        $this->logStatus($exit, 'Approved', $request->input('Remarks') ?? 'Exit request approved.');

        return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Exit request approved.');
    }

    public function reject(Request $request, $id)
    {
        $exit = ExitRequest::findOrFail($id);
        $exit->update([
            'ApprovalStatus' => 'Rejected',
            'Status' => 'Rejected',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        $this->logStatus($exit, 'Rejected', $request->input('Remarks') ?? 'Exit request rejected.');

        return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Exit request rejected.');
    }

    public function close(Request $request, $id, ExitService $exitService)
    {
        $exit = ExitRequest::with(['clearances', 'employee'])->findOrFail($id);

        $pending = $exit->clearances->first(function ($clearance) {
            return $clearance->Status !== 'Cleared';
        });

        if ($pending) {
            return redirect()->route('hr.exit.requests.show', $exit->Id)
                ->withErrors(['clearance' => 'All clearance items must be cleared before closing the exit.']);
        }

        $exit->update([
            'Status' => 'Closed',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        if ($exit->employee) {
            $exit->employee->update([
                'Status' => 'Exited',
                'IsActive' => 0,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);

            MonthlyAllowance::where('EmployeeID', $exit->employee->Id)
                ->where('IsRecurring', 1)
                ->update([
                    'IsRecurring' => 0,
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]);

            MonthlyDeduction::where('EmployeeID', $exit->employee->Id)
                ->where('IsRecurring', 1)
                ->update([
                    'IsRecurring' => 0,
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]);

            $exitService->deactivateLinkedUser($exit->employee, auth()->id());
        }

        $this->logStatus($exit, 'Closed', $request->input('Remarks') ?? 'Exit closed.');

        return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Exit request closed.');
    }

    public function storeDocument(Request $request, $id)
    {
        $exit = ExitRequest::findOrFail($id);
        $data = $request->validate([
            'Document' => ['required', 'file', 'max:5120', 'mimes:pdf,doc,docx,xls,xlsx,csv,png,jpg,jpeg'],
            'DocType' => ['nullable', 'string', 'max:50'],
        ]);

        $document = DocumentService::createUpload(
            RepositoryService::module(ModulesEnum::HRM),
            $request->file('Document'),
            auth()->user()
        )->document;

        ExitDocument::create([
            'ExitID' => $exit->Id,
            'DocumentId' => $document->Id,
            'DocType' => $data['DocType'] ?? 'Exit Document',
            'UploadedBy' => auth()->id(),
            'UploadedOn' => now(),
        ]);

        return redirect()->route('hr.exit.requests.show', $exit->Id)->with('success', 'Document uploaded.');
    }

    private function generateExitNo(): string
    {
        return 'EXIT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
    }

    private function logStatus(ExitRequest $exit, string $toStatus, ?string $remarks = null): void
    {
        ExitStatusLog::create([
            'ExitID' => $exit->Id,
            'FromStatus' => $exit->Status,
            'ToStatus' => $toStatus,
            'Remarks' => $remarks,
            'ChangedBy' => auth()->id(),
            'ChangedOn' => now(),
        ]);

        if ($exit->Status !== $toStatus) {
            $exit->update([
                'Status' => $toStatus,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => now(),
            ]);
        }
    }

    private function syncClearances(ExitRequest $exit): void
    {
        ExitClearanceDepartment::syncFromDepartments(auth()->id());
        $departmentIds = ExitClearanceDepartment::where('IsActive', 1)->pluck('Id');
        $existing = $exit->clearances()->pluck('ClearanceDepartmentID')->unique();
        $missing = $departmentIds->diff($existing);

        foreach ($missing as $deptId) {
            ExitClearance::create([
                'ExitID' => $exit->Id,
                'ClearanceDepartmentID' => $deptId,
                'Status' => 'Pending',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }
    }

}
