<?php

namespace App\Http\Controllers\HR;

use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HR\TrainingCertificate;
use App\Models\HR\TrainingProgram;
use App\Models\HR\TrainingSession;
use App\Models\HR\TrainingSessionFeedback;
use App\Models\HR\TrainingSessionParticipant;
use App\Models\HR\TrainingTrainer;
use App\Models\HRM\Department;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Illuminate\Http\Request;

class TrainingSessionController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingSession::with(['program', 'trainer']);

        if ($request->filled('program_id')) {
            $query->where('ProgramID', $request->program_id);
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }

        $sessions = $query->orderByDesc('StartDate')->paginate(30);
        $programs = TrainingProgram::orderBy('Title')->get(['Id', 'Title']);
        $statusList = ['Planned', 'Open', 'Ongoing', 'Completed', 'Cancelled'];

        return view('hr.training.sessions.index', compact('sessions', 'programs', 'statusList'));
    }

    public function create()
    {
        $programs = TrainingProgram::orderBy('Title')->get();
        $trainers = TrainingTrainer::with('employee')->orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::orderBy('Name')->get(['Id', 'Name']);
        $employees = Employee::orderBy('FirstName')->get([
            'Id',
            'FirstName',
            'LastName',
            'EmployeeNo',
            'DepartmentID',
            'GradeID',
            'RoleID',
        ]);

        return view('hr.training.sessions.create', compact('programs', 'trainers', 'departments', 'grades', 'roles', 'employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ProgramID' => ['required', 'exists:t_HRTrainingPrograms,Id'],
            'Title' => ['nullable', 'string', 'max:200'],
            'SessionCode' => ['nullable', 'string', 'max:30'],
            'StartDate' => ['nullable', 'date'],
            'EndDate' => ['nullable', 'date'],
            'StartTime' => ['nullable', 'date_format:H:i'],
            'EndTime' => ['nullable', 'date_format:H:i'],
            'Location' => ['nullable', 'string', 'max:150'],
            'OnlineLink' => ['nullable', 'string', 'max:255'],
            'TrainerID' => ['nullable', 'exists:t_HRTrainingTrainers,Id'],
            'MaxParticipants' => ['nullable', 'integer', 'min:1'],
            'Status' => ['nullable', 'string', 'max:30'],
            'AgendaFile' => ['nullable', 'file', 'max:5120'],
            'MaterialsFile' => ['nullable', 'file', 'max:5120'],
            'EmployeeIDs' => ['array'],
            'EmployeeIDs.*' => ['integer', 'exists:t_HREmployees,Id'],
            'EnrollDepartments' => ['array'],
            'EnrollDepartments.*' => ['integer', 'exists:t_Departments,Id'],
            'EnrollGrades' => ['array'],
            'EnrollGrades.*' => ['integer', 'exists:t_HRJobGrades,Id'],
            'EnrollRoles' => ['array'],
            'EnrollRoles.*' => ['integer', 'exists:t_Roles,id'],
        ]);

        $program = TrainingProgram::find($data['ProgramID']);
        $data['Title'] = $data['Title'] ?: ($program?->Title ?? null);
        $data['Status'] = $data['Status'] ?? 'Planned';
        $data['StartTime'] = $this->normalizeTime($data['StartTime'] ?? null);
        $data['EndTime'] = $this->normalizeTime($data['EndTime'] ?? null);
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $session = TrainingSession::create($data);

        $this->attachSessionDocuments($session, $request);
        $this->syncParticipants($session, $request);

        return redirect()->route('hr.training.sessions.index')
            ->with('success', 'Training session created.');
    }

    public function show($id)
    {
        $session = TrainingSession::with(['program', 'trainer', 'participants.employee'])->findOrFail($id);
        $participants = $session->participants;
        $certificates = TrainingCertificate::where('SessionID', $session->Id)->get()->keyBy('EmployeeID');

        $feedback = TrainingSessionFeedback::where('SessionID', $session->Id)->get()->keyBy('EmployeeID');

        return view('hr.training.sessions.show', compact('session', 'participants', 'certificates', 'feedback'));
    }

    public function edit($id)
    {
        $session = TrainingSession::findOrFail($id);
        $programs = TrainingProgram::orderBy('Title')->get();
        $trainers = TrainingTrainer::with('employee')->orderBy('Name')->get();
        $departments = Department::orderBy('Name')->get(['Id', 'Name']);
        $grades = JobGrade::orderBy('Name')->get(['Id', 'Name']);
        $roles = JobRole::orderBy('Name')->get(['Id', 'Name']);
        $employees = Employee::orderBy('FirstName')->get([
            'Id',
            'FirstName',
            'LastName',
            'EmployeeNo',
            'DepartmentID',
            'GradeID',
            'RoleID',
        ]);

        return view('hr.training.sessions.edit', compact('session', 'programs', 'trainers', 'departments', 'grades', 'roles', 'employees'));
    }

    public function update(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);

        $data = $request->validate([
            'ProgramID' => ['required', 'exists:t_HRTrainingPrograms,Id'],
            'Title' => ['nullable', 'string', 'max:200'],
            'SessionCode' => ['nullable', 'string', 'max:30'],
            'StartDate' => ['nullable', 'date'],
            'EndDate' => ['nullable', 'date'],
            'StartTime' => ['nullable', 'date_format:H:i'],
            'EndTime' => ['nullable', 'date_format:H:i'],
            'Location' => ['nullable', 'string', 'max:150'],
            'OnlineLink' => ['nullable', 'string', 'max:255'],
            'TrainerID' => ['nullable', 'exists:t_HRTrainingTrainers,Id'],
            'MaxParticipants' => ['nullable', 'integer', 'min:1'],
            'Status' => ['nullable', 'string', 'max:30'],
            'AgendaFile' => ['nullable', 'file', 'max:5120'],
            'MaterialsFile' => ['nullable', 'file', 'max:5120'],
        ]);

        $program = TrainingProgram::find($data['ProgramID']);
        $data['Title'] = $data['Title'] ?: ($program?->Title ?? $session->Title);
        $data['Status'] = $data['Status'] ?? $session->Status;
        $data['StartTime'] = $this->normalizeTime($data['StartTime'] ?? null);
        $data['EndTime'] = $this->normalizeTime($data['EndTime'] ?? null);
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $session->update($data);

        $this->attachSessionDocuments($session, $request);

        return redirect()->route('hr.training.sessions.show', $session->Id)
            ->with('success', 'Session updated.');
    }

    public function addParticipants(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);
        $this->syncParticipants($session, $request);

        return redirect()->route('hr.training.sessions.show', $session->Id)
            ->with('success', 'Participants updated.');
    }

    public function updateParticipant(Request $request, $id, $participantId)
    {
        $session = TrainingSession::findOrFail($id);
        $participant = TrainingSessionParticipant::where('SessionID', $id)->findOrFail($participantId);

        $rsvpStatuses = ['Nominated', 'Approved', 'Confirmed', 'Declined', 'Withdrawn', 'Completed'];
        $attendanceStatuses = ['Present', 'Late', 'Absent', 'No-show'];

        $action = $request->input('Action', 'rsvp');
        $canUpdateRsvp = in_array($session->Status, ['Planned', 'Open'], true);
        $canMarkAttendance = in_array($session->Status, ['Ongoing', 'Completed'], true);

        if ($action === 'attendance') {
            if (! $canMarkAttendance) {
                return redirect()->route('hr.training.sessions.show', $id)
                    ->withErrors(['attendance' => 'Attendance can only be marked when the session is ongoing or completed.']);
            }
            if (! in_array($participant->Status, ['Approved', 'Confirmed', 'Completed'], true)) {
                return redirect()->route('hr.training.sessions.show', $id)
                    ->withErrors(['attendance' => 'Attendance can only be marked for confirmed participants.']);
            }

            $data = $request->validate([
                'AttendanceStatus' => ['required', 'string', 'max:30', 'in:' . implode(',', $attendanceStatuses)],
            ]);

            $data['AttendanceMarkedOn'] = now();
            $data['AttendanceMarkedBy'] = auth()->id();
            $data['Status'] = $participant->Status ?: 'Nominated';
        } else {
            if (! $canUpdateRsvp) {
                return redirect()->route('hr.training.sessions.show', $id)
                    ->withErrors(['rsvp' => 'RSVP can only be updated before the session starts.']);
            }

            $data = $request->validate([
                'Status' => ['required', 'string', 'max:30', 'in:' . implode(',', $rsvpStatuses)],
            ]);
        }

        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $participant->update($data);

        return redirect()->route('hr.training.sessions.show', $id)
            ->with('success', 'Participant updated.');
    }

    public function issueCertificate(Request $request, $id, $participantId)
    {
        $session = TrainingSession::with('program')->findOrFail($id);
        $participant = TrainingSessionParticipant::where('SessionID', $id)->findOrFail($participantId);

        $data = $request->validate([
            'CertificationName' => ['nullable', 'string', 'max:200'],
            'IssuingBody' => ['nullable', 'string', 'max:150'],
            'CertificateNumber' => ['nullable', 'string', 'max:50'],
            'IssuedOn' => ['nullable', 'date'],
            'ExpiresOn' => ['nullable', 'date'],
            'CertificateFile' => ['nullable', 'file', 'max:5120'],
        ]);

        $existing = TrainingCertificate::where('SessionID', $session->Id)
            ->where('EmployeeID', $participant->EmployeeID)
            ->first();

        if ($existing) {
            return redirect()->route('hr.training.sessions.show', $id)
                ->with('error', 'Certificate already issued for this participant.');
        }

        $documentId = null;
        if ($request->hasFile('CertificateFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::HRM),
                $request->file('CertificateFile'),
                auth()->user()
            )->document;
            $documentId = $document->Id;
        }

        TrainingCertificate::create([
            'SessionID' => $session->Id,
            'EmployeeID' => $participant->EmployeeID,
            'CertificationName' => $data['CertificationName'] ?: ($session->program?->Title ?? 'Training Certificate'),
            'IssuingBody' => $data['IssuingBody'] ?? null,
            'CertificateNumber' => $data['CertificateNumber'] ?? null,
            'IssuedOn' => $data['IssuedOn'] ?? now()->toDateString(),
            'ExpiresOn' => $data['ExpiresOn'] ?? null,
            'DocumentId' => $documentId,
            'Status' => 'Valid',
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        $participant->update([
            'Status' => 'Completed',
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.training.sessions.show', $id)
            ->with('success', 'Certificate issued.');
    }

    public function saveFeedback(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);
        $data = $request->validate([
            'Feedback' => ['array'],
            'Feedback.*.RatingContent' => ['nullable', 'integer', 'min:1', 'max:5'],
            'Feedback.*.RatingTrainer' => ['nullable', 'integer', 'min:1', 'max:5'],
            'Feedback.*.RatingRelevance' => ['nullable', 'integer', 'min:1', 'max:5'],
            'Feedback.*.Comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $participantIds = TrainingSessionParticipant::where('SessionID', $session->Id)
            ->pluck('EmployeeID')
            ->map(fn ($id) => (int)$id)
            ->toArray();

        $now = now();
        foreach ($data['Feedback'] ?? [] as $employeeId => $row) {
            $employeeId = (int)$employeeId;
            if (! in_array($employeeId, $participantIds, true)) {
                continue;
            }

            $hasValue = ! empty($row['RatingContent'])
                || ! empty($row['RatingTrainer'])
                || ! empty($row['RatingRelevance'])
                || ! empty($row['Comments']);
            if (! $hasValue) {
                continue;
            }

            $feedback = TrainingSessionFeedback::firstOrNew([
                'SessionID' => $session->Id,
                'EmployeeID' => $employeeId,
            ]);

            $feedback->fill([
                'RatingContent' => $row['RatingContent'] ?? null,
                'RatingTrainer' => $row['RatingTrainer'] ?? null,
                'RatingRelevance' => $row['RatingRelevance'] ?? null,
                'Comments' => $row['Comments'] ?? null,
                'ModifiedBy' => auth()->id(),
                'ModifiedOn' => $now,
            ]);

            if (! $feedback->exists) {
                $feedback->CreatedBy = auth()->id();
                $feedback->CreatedOn = $now;
            }

            $feedback->save();
        }

        return redirect()->route('hr.training.sessions.show', $session->Id)
            ->with('success', 'Feedback saved.');
    }

    private function attachSessionDocuments(TrainingSession $session, Request $request): void
    {
        if ($request->hasFile('AgendaFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::HRM),
                $request->file('AgendaFile'),
                auth()->user()
            )->document;
            $session->update(['AgendaDocumentId' => $document->Id]);
        }

        if ($request->hasFile('MaterialsFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::HRM),
                $request->file('MaterialsFile'),
                auth()->user()
            )->document;
            $session->update(['MaterialsDocumentId' => $document->Id]);
        }
    }

    private function syncParticipants(TrainingSession $session, Request $request): void
    {
        $employeeIds = collect($request->input('EmployeeIDs', []));

        $departmentIds = $request->input('EnrollDepartments', []);
        $gradeIds = $request->input('EnrollGrades', []);
        $roleIds = $request->input('EnrollRoles', []);

        if (! empty($departmentIds)) {
            $employeeIds = $employeeIds->merge(
                Employee::whereIn('DepartmentID', $departmentIds)->pluck('Id')
            );
        } elseif (! empty($gradeIds)) {
            $employeeIds = $employeeIds->merge(
                Employee::whereIn('GradeID', $gradeIds)->pluck('Id')
            );
        } elseif (! empty($roleIds)) {
            $employeeIds = $employeeIds->merge(
                Employee::whereIn('RoleID', $roleIds)->pluck('Id')
            );
        }

        $employeeIds = $employeeIds->unique()->values();
        if ($employeeIds->isEmpty()) {
            return;
        }

        $existing = TrainingSessionParticipant::where('SessionID', $session->Id)
            ->whereIn('EmployeeID', $employeeIds)
            ->pluck('EmployeeID')
            ->toArray();

        $now = now();
        $insert = [];
        foreach ($employeeIds as $employeeId) {
            if (in_array($employeeId, $existing, true)) {
                continue;
            }
            $insert[] = [
                'SessionID' => $session->Id,
                'EmployeeID' => $employeeId,
                'EnrollmentMethod' => 'HR',
                'Status' => 'Nominated',
                'EnrolledOn' => $now,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
            ];
        }

        if (! empty($insert)) {
            TrainingSessionParticipant::insert($insert);
        }
    }

    private function normalizeTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }
        if (strlen($value) === 5) {
            return $value . ':00';
        }

        return $value;
    }
}
