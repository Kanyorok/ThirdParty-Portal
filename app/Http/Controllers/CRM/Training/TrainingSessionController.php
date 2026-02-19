<?php

namespace App\Http\Controllers\CRM\Training;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\CRM\Training\TrainingCertificate;
use App\Models\CRM\Training\TrainingCertificateTemplate;
use App\Models\CRM\Training\TrainingProgram;
use App\Models\CRM\Training\TrainingProgramTarget;
use App\Models\CRM\Training\TrainingSession;
use App\Models\CRM\Training\TrainingSessionFeedback;
use App\Models\CRM\Training\TrainingSessionParticipant;
use App\Models\CRM\Training\TrainingTrainer;
use App\Models\DMS\Document;
use App\Services\CRMEmailService;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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

        return view('crm.training.sessions.index', compact('sessions', 'programs', 'statusList'));
    }

    public function create()
    {
        $programs = TrainingProgram::orderBy('Title')->get();
        $trainers = TrainingTrainer::with('user')->orderBy('Name')->get();
        $oldClientIds = collect((array) old('ClientIDs', []))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();
        $selectedClients = $this->loadClientsByIds($oldClientIds);

        return view('crm.training.sessions.create', compact('programs', 'trainers', 'selectedClients'));
    }

    public function programClients(Request $request): JsonResponse
    {
        $programId = (int) $request->query('program_id', 0);
        if ($programId <= 0) {
            return response()->json(['results' => []]);
        }

        $search = trim((string) $request->query('q', ''));
        $sessionId = (int) $request->query('session_id', 0);

        $query = Client::query()
            ->with([
                'individual:ClientID,PassportNo',
                'corporate:ClientID,CertificateNo',
            ])
            ->select(['ClientID', 'Name'])
            ->whereExists(function ($subQuery) use ($programId) {
                $subQuery->selectRaw('1')
                    ->from('t_CRMTrainingProgramTargets as tgt')
                    ->whereColumn('tgt.TargetID', 'syn_t_Client.ClientID')
                    ->where('tgt.ProgramID', $programId)
                    ->where('tgt.TargetType', 'Client');
            });

        if ($sessionId > 0) {
            $query->whereNotExists(function ($subQuery) use ($sessionId) {
                $subQuery->selectRaw('1')
                    ->from('t_CRMTrainingSessionParticipants as tsp')
                    ->whereColumn('tsp.ClientID', 'syn_t_Client.ClientID')
                    ->where('tsp.SessionID', $sessionId);
            });
        }

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery->where('Name', 'like', '%' . $search . '%')
                    ->orWhere('ClientID', 'like', '%' . $search . '%')
                    ->orWhereHas('individual', function (Builder $individualQuery) use ($search) {
                        $individualQuery->where('PassportNo', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('corporate', function (Builder $corporateQuery) use ($search) {
                        $corporateQuery->where('CertificateNo', 'like', '%' . $search . '%');
                    });
            });
        }

        $results = $query->orderBy('Name')
            ->limit(30)
            ->get()
            ->map(function (Client $client) {
                $idNumber = $client->individual?->PassportNo ?? $client->corporate?->CertificateNo;

                return [
                    'id' => $client->ClientID,
                    'text' => $client->Name . ' (' . $client->ClientID . ')' . ($idNumber ? ' - ' . $idNumber : ''),
                ];
            })
            ->values();

        return response()->json(['results' => $results]);
    }

    public function sessionParticipantsSearch(Request $request, $id): JsonResponse
    {
        $session = TrainingSession::findOrFail($id);
        $search = trim((string) $request->query('q', ''));

        $query = Client::query()
            ->with([
                'individual:ClientID,PassportNo',
                'corporate:ClientID,CertificateNo',
            ])
            ->select(['ClientID', 'Name'])
            ->whereExists(function ($subQuery) use ($session) {
                $subQuery->selectRaw('1')
                    ->from('t_CRMTrainingSessionParticipants as tsp')
                    ->whereColumn('tsp.ClientID', 'syn_t_Client.ClientID')
                    ->where('tsp.SessionID', $session->Id);
            });

        if ($search !== '') {
            $query->where(function (Builder $searchQuery) use ($search) {
                $searchQuery->where('Name', 'like', '%' . $search . '%')
                    ->orWhere('ClientID', 'like', '%' . $search . '%')
                    ->orWhereHas('individual', function (Builder $individualQuery) use ($search) {
                        $individualQuery->where('PassportNo', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('corporate', function (Builder $corporateQuery) use ($search) {
                        $corporateQuery->where('CertificateNo', 'like', '%' . $search . '%');
                    });
            });
        }

        $results = $query->orderBy('Name')
            ->limit(30)
            ->get()
            ->map(function (Client $client) {
                $idNumber = $client->individual?->PassportNo ?? $client->corporate?->CertificateNo;

                return [
                    'id' => (string) $client->ClientID,
                    'name' => $client->Name,
                    'text' => $client->Name . ' (' . $client->ClientID . ')' . ($idNumber ? ' - ' . $idNumber : ''),
                ];
            })
            ->values();

        return response()->json(['results' => $results]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'ProgramID' => ['required', 'exists:t_CRMTrainingPrograms,Id'],
            'Title' => ['nullable', 'string', 'max:200'],
            'SessionCode' => ['nullable', 'string', 'max:30'],
            'StartDate' => ['nullable', 'date'],
            'EndDate' => ['nullable', 'date'],
            'StartTime' => ['nullable', 'date_format:H:i'],
            'EndTime' => ['nullable', 'date_format:H:i'],
            'Location' => ['nullable', 'string', 'max:150'],
            'OnlineLink' => ['nullable', 'string', 'max:255'],
            'TrainerID' => ['nullable', 'exists:t_CRMTrainingTrainers,Id'],
            'MaxParticipants' => ['nullable', 'integer', 'min:1'],
            'Status' => ['nullable', 'string', 'max:30'],
            'AgendaFile' => ['nullable', 'file', 'max:5120'],
            'MaterialsFile' => ['nullable', 'file', 'max:5120'],
            'AutoAddProgramParticipants' => ['sometimes', 'boolean'],
            'ClientIDs' => ['array'],
            'ClientIDs.*' => ['string', 'max:50'],
        ]);

        $autoAddProgramParticipants = $request->boolean('AutoAddProgramParticipants', false);

        if ($autoAddProgramParticipants) {
            $requestedClientIds = $this->getProgramTargetClientIds((int) $data['ProgramID']);
            if ($requestedClientIds->isEmpty()) {
                throw ValidationException::withMessages([
                    'AutoAddProgramParticipants' => 'This program has no registered participants to add.',
                ]);
            }
        } else {
            $requestedClientIds = collect((array) $request->input('ClientIDs', []))
                ->map(fn ($id) => trim((string) $id))
                ->filter()
                ->unique()
                ->values();
        }

        $validClientIds = $this->validateProgramParticipantSelection((int) $data['ProgramID'], $requestedClientIds);

        if (is_numeric($data['MaxParticipants'] ?? null)) {
            $maxParticipants = (int) $data['MaxParticipants'];
            if ($validClientIds->count() > $maxParticipants) {
                throw ValidationException::withMessages([
                    'MaxParticipants' => 'Max participants is ' . $maxParticipants . ', but you are trying to add ' . $validClientIds->count() . ' participant(s).',
                ]);
            }
        }

        $program = TrainingProgram::find($data['ProgramID']);
        $data['Title'] = $data['Title'] ?: ($program?->Title ?? null);
        $data['Status'] = $data['Status'] ?? 'Planned';
        $data['StartTime'] = $this->normalizeTime($data['StartTime'] ?? null);
        $data['EndTime'] = $this->normalizeTime($data['EndTime'] ?? null);
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $duplicateSession = $this->findRecentDuplicateSession($data);
        if ($duplicateSession) {
            return redirect()->route('crm.training.sessions.show', $duplicateSession->Id)
                ->with('success', 'Session was already saved. Opened the existing record.');
        }

        [$session, $inserted] = DB::transaction(function () use ($data, $request, $validClientIds) {
            $session = TrainingSession::create($data);
            $this->attachSessionDocuments($session, $request);
            $inserted = $this->syncParticipants($session, $validClientIds);

            return [$session, $inserted];
        });

        if ($autoAddProgramParticipants && $inserted > 0) {
            return redirect()->route('crm.training.sessions.index')
                ->with('success', 'Training session created. ' . $inserted . ' participant(s) auto-added from program list.');
        }

        return redirect()->route('crm.training.sessions.index')
            ->with('success', 'Training session created.');
    }

    public function show($id)
    {
        $session = TrainingSession::with(['program', 'trainer', 'participants.client'])->findOrFail($id);
        $participants = $session->participants;
        $certificates = TrainingCertificate::query()
            ->where('SessionID', $session->Id)
            ->where(function (Builder $query) {
                $query->whereNull('CertificateScope')
                    ->orWhere('CertificateScope', 'Session');
            })
            ->get()
            ->keyBy('ClientID');
        $bulkCertificateState = $this->getBulkCertificateState((int) $session->Id);
        $certificateTemplates = TrainingCertificateTemplate::query()
            ->where('IsActive', 1)
            ->where(function (Builder $query) use ($session) {
                $query->whereNull('ProgramID')
                    ->orWhere('ProgramID', $session->ProgramID);
            })
            ->orderByDesc('IsSample')
            ->orderBy('Name')
            ->get();

        return view('crm.training.sessions.show', compact('session', 'participants', 'certificates', 'certificateTemplates', 'bulkCertificateState'));
    }

    public function feedbackForm($id)
    {
        $session = TrainingSession::with(['program', 'trainer', 'participants.client'])->findOrFail($id);
        $participants = $session->participants;
        $feedback = TrainingSessionFeedback::where('SessionID', $session->Id)->get()->keyBy('ClientID');

        return view('crm.training.sessions.feedback', compact('session', 'participants', 'feedback'));
    }

    public function edit($id)
    {
        $session = TrainingSession::findOrFail($id);
        $programs = TrainingProgram::orderBy('Title')->get();
        $trainers = TrainingTrainer::with('user')->orderBy('Name')->get();
        $oldClientIds = collect((array) old('ClientIDs', []))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();
        $selectedClients = $this->loadClientsByIds($oldClientIds);
        $currentParticipantsCount = TrainingSessionParticipant::where('SessionID', $session->Id)->count();
        $remainingSlots = is_numeric($session->MaxParticipants)
            ? max(((int) $session->MaxParticipants) - $currentParticipantsCount, 0)
            : null;

        return view('crm.training.sessions.edit', compact(
            'session',
            'programs',
            'trainers',
            'selectedClients',
            'currentParticipantsCount',
            'remainingSlots'
        ));
    }

    public function update(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);

        $data = $request->validate([
            'ProgramID' => ['required', 'exists:t_CRMTrainingPrograms,Id'],
            'Title' => ['nullable', 'string', 'max:200'],
            'SessionCode' => ['nullable', 'string', 'max:30'],
            'StartDate' => ['nullable', 'date'],
            'EndDate' => ['nullable', 'date'],
            'StartTime' => ['nullable', 'date_format:H:i'],
            'EndTime' => ['nullable', 'date_format:H:i'],
            'Location' => ['nullable', 'string', 'max:150'],
            'OnlineLink' => ['nullable', 'string', 'max:255'],
            'TrainerID' => ['nullable', 'exists:t_CRMTrainingTrainers,Id'],
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

        return redirect()->route('crm.training.sessions.show', $session->Id)
            ->with('success', 'Session updated.');
    }

    public function addParticipants(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);
        $request->validate([
            'ClientIDs' => ['required', 'array', 'min:1'],
            'ClientIDs.*' => ['string', 'max:50'],
        ]);

        $requestedClientIds = collect((array) $request->input('ClientIDs', []))
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();
        $validClientIds = $this->validateProgramParticipantSelection((int) $session->ProgramID, $requestedClientIds);

        $inserted = $this->syncParticipants($session, $validClientIds);

        if ($inserted === 0) {
            return redirect()->route('crm.training.sessions.show', $session->Id)
                ->with('success', 'Selected clients are already participants in this session.');
        }

        return redirect()->route('crm.training.sessions.show', $session->Id)
            ->with('success', $inserted . ' participant(s) added.');
    }

    public function addAllParticipants($id)
    {
        $session = TrainingSession::findOrFail($id);
        $validClientIds = Client::query()
            ->select('ClientID')
            ->whereExists(function ($subQuery) use ($session) {
                $subQuery->selectRaw('1')
                    ->from('t_CRMTrainingProgramTargets as tgt')
                    ->whereColumn('tgt.TargetID', 'syn_t_Client.ClientID')
                    ->where('tgt.ProgramID', (int) $session->ProgramID)
                    ->where('tgt.TargetType', 'Client');
            })
            ->pluck('ClientID')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        if ($validClientIds->isEmpty()) {
            return redirect()->route('crm.training.sessions.show', $session->Id)
                ->withErrors(['participants' => 'This program has no registered participants.']);
        }

        $inserted = $this->syncParticipants($session, $validClientIds);
        if ($inserted === 0) {
            return redirect()->route('crm.training.sessions.show', $session->Id)
                ->with('success', 'All registered program participants are already in this session.');
        }

        return redirect()->route('crm.training.sessions.show', $session->Id)
            ->with('success', $inserted . ' participant(s) added from the program participant list.');
    }

    public function updateParticipant(Request $request, $id, $participantId)
    {
        $session = TrainingSession::findOrFail($id);
        $participant = TrainingSessionParticipant::where('SessionID', $id)->findOrFail($participantId);

        if (! $this->canMarkAttendance($session)) {
            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['attendance' => 'Attendance cannot be marked for cancelled sessions.']);
        }

        $data = $request->validate([
            'AttendanceStatus' => ['required', 'string', 'max:30', 'in:Present,Absent'],
        ]);

        $now = now();
        $participant->update([
            'AttendanceStatus' => $data['AttendanceStatus'],
            'AttendanceMarkedOn' => $now,
            'AttendanceMarkedBy' => auth()->id(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => $now,
            'Status' => $participant->Status ?: 'Nominated',
        ]);

        $attendanceLabel = $data['AttendanceStatus'] === 'Present' ? 'Attended' : 'Not Attended';

        return redirect()->route('crm.training.sessions.show', $id)
            ->with('success', 'Participant marked as ' . $attendanceLabel . '.');
    }

    public function bulkAttendance(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);

        if (! $this->canMarkAttendance($session)) {
            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['attendance' => 'Attendance cannot be marked for cancelled sessions.']);
        }

        $data = $request->validate([
            'AttendanceStatus' => ['required', 'string', 'max:30', 'in:Present,Absent'],
        ]);

        $participantQuery = TrainingSessionParticipant::where('SessionID', $session->Id);
        $participantsCount = (clone $participantQuery)->count();
        if ($participantsCount === 0) {
            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['attendance' => 'No participants found for bulk attendance update.']);
        }

        $now = now();
        $updated = $participantQuery->update([
            'AttendanceStatus' => $data['AttendanceStatus'],
            'AttendanceMarkedOn' => $now,
            'AttendanceMarkedBy' => auth()->id(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => $now,
        ]);

        $attendanceLabel = $data['AttendanceStatus'] === 'Present' ? 'Attended' : 'Not Attended';

        return redirect()->route('crm.training.sessions.show', $id)
            ->with('success', $updated . ' participant(s) marked as ' . $attendanceLabel . '.');
    }

    public function issueCertificate(Request $request, $id, $participantId)
    {
        $session = TrainingSession::with('program')->findOrFail($id);
        $participant = TrainingSessionParticipant::with('client')->where('SessionID', $id)->findOrFail($participantId);

        if (($session->program?->CertificateScope ?? 'Session') === 'Program') {
            return redirect()->route('crm.training.programs.certification', $session->ProgramID)
                ->withErrors(['certificate' => 'This program issues certificates at program level. Use Program Certification.']);
        }

        $data = $request->validate([
            'CertificateTemplateID' => ['nullable', 'integer', 'exists:t_CRMTrainingCertificateTemplates,Id'],
            'CertificateTemplateName' => ['nullable', 'string', 'max:150'],
            'CertificateTemplateFile' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,svg'],
            'CertificationName' => ['nullable', 'string', 'max:200'],
            'IssuingBody' => ['nullable', 'string', 'max:150'],
            'CertificateNumber' => ['nullable', 'string', 'max:50'],
            'IssuedOn' => ['nullable', 'date'],
            'ExpiresOn' => ['nullable', 'date'],
            'CertificateFile' => ['nullable', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'],
            'SendEmail' => ['sometimes', 'boolean'],
        ]);

        $existing = TrainingCertificate::query()
            ->where('SessionID', $session->Id)
            ->where(function (Builder $query) {
                $query->whereNull('CertificateScope')
                    ->orWhere('CertificateScope', 'Session');
            })
            ->where('ClientID', $participant->ClientID)
            ->first();

        if ($existing) {
            return redirect()->route('crm.training.sessions.show', $id)
                ->with('error', 'Certificate already issued for this participant.');
        }

        $selectedTemplate = null;
        if (! empty($data['CertificateTemplateID'])) {
            $selectedTemplate = TrainingCertificateTemplate::query()
                ->where('Id', (int) $data['CertificateTemplateID'])
                ->where('IsActive', 1)
                ->where(function (Builder $query) use ($session) {
                    $query->whereNull('ProgramID')
                        ->orWhere('ProgramID', $session->ProgramID);
                })
                ->first();

            if (! $selectedTemplate) {
                return redirect()->route('crm.training.sessions.show', $id)
                    ->withErrors(['certificate' => 'Selected certificate template is not available for this program.'])
                    ->withInput();
            }
        }

        if ($request->hasFile('CertificateTemplateFile')) {
            $templateDocument = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::CRM),
                $request->file('CertificateTemplateFile'),
                auth()->user()
            )->document;

            $templateName = trim((string) ($data['CertificateTemplateName'] ?? ''));
            if ($templateName === '') {
                $templateName = 'Custom Template - ' . ($session->program?->Title ?? 'Training');
            }

            $selectedTemplate = TrainingCertificateTemplate::create([
                'ProgramID' => (int) $session->ProgramID,
                'Name' => $templateName,
                'Description' => 'Uploaded from certificate issue form.',
                'DefaultIssuingBody' => $data['IssuingBody'] ?? null,
                'DefaultValidityMonths' => null,
                'TemplateDocumentId' => $templateDocument->Id,
                'IsSample' => false,
                'IsActive' => true,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        $issuedOn = $data['IssuedOn'] ?? now()->toDateString();
        $expiresOn = $data['ExpiresOn'] ?? null;
        if (! $expiresOn && $selectedTemplate && is_numeric($selectedTemplate->DefaultValidityMonths)) {
            $expiresOn = Carbon::parse($issuedOn)->addMonths((int) $selectedTemplate->DefaultValidityMonths)->toDateString();
        }

        $certificationName = $data['CertificationName']
            ?: ($selectedTemplate?->Name ?: ($session->program?->Title ?? 'Training Certificate'));
        $issuingBody = $data['IssuingBody'] ?? $selectedTemplate?->DefaultIssuingBody;

        $documentId = null;
        if ($request->hasFile('CertificateFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::CRM),
                $request->file('CertificateFile'),
                auth()->user()
            )->document;
            $documentId = $document->Id;
        } else {
            try {
                $documentId = $this->generateCertificateDocument(
                    session: $session,
                    participant: $participant,
                    selectedTemplate: $selectedTemplate,
                    certificationName: $certificationName,
                    issuingBody: $issuingBody,
                    certificateNumber: $data['CertificateNumber'] ?? null,
                    issuedOn: $issuedOn,
                    expiresOn: $expiresOn
                );
            } catch (\Throwable $e) {
                report($e);

                return redirect()->route('crm.training.sessions.show', $id)
                    ->withErrors(['certificate' => 'Certificate was not issued because document generation failed.'])
                    ->withInput();
            }
        }

        $certificate = TrainingCertificate::create([
            'SessionID' => $session->Id,
            'ProgramID' => (int) $session->ProgramID,
            'CertificateScope' => 'Session',
            'TemplateID' => $selectedTemplate?->Id,
            'ClientID' => $participant->ClientID,
            'CertificationName' => $certificationName,
            'IssuingBody' => $issuingBody,
            'CertificateNumber' => $data['CertificateNumber'] ?? null,
            'IssuedOn' => $issuedOn,
            'ExpiresOn' => $expiresOn,
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

        $emailNotice = '';
        if ($request->boolean('SendEmail', false)) {
            $emailSent = $this->sendCertificateEmail($session, $participant, $certificate);
            if ($emailSent) {
                $emailNotice = ' Certificate emailed to participant.';
            } else {
                $emailNotice = ' Certificate issued, but email was not sent (missing/invalid client email or delivery failure).';
            }
        }

        return redirect()->route('crm.training.sessions.show', $id)
            ->with('success', 'Certificate issued.' . $emailNotice);
    }

    public function bulkIssueCertificates(Request $request, $id)
    {
        @ini_set('memory_limit', '512M');
        $session = TrainingSession::with('program')->findOrFail($id);

        if (! $session->program?->HasCertification) {
            $message = 'This program does not support certification.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['certificate' => $message]);
        }

        if (($session->program?->CertificateScope ?? 'Session') === 'Program') {
            $message = 'This program issues certificates at program level. Use Program Certification.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('crm.training.programs.certification', $session->ProgramID)
                ->withErrors(['certificate' => $message]);
        }

        $currentState = $this->getBulkCertificateState((int) $session->Id);
        if (in_array(($currentState['status'] ?? 'idle'), ['processing', 'cancelling'], true)) {
            $message = 'Bulk certificate issuance is already running for this session.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'run' => $currentState,
                ], 409);
            }

            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['certificate' => $message]);
        }

        $data = $request->validate([
            'CertificateTemplateID' => ['required', 'integer', 'exists:t_CRMTrainingCertificateTemplates,Id'],
            'CertificationName' => ['nullable', 'string', 'max:200'],
            'IssuingBody' => ['nullable', 'string', 'max:150'],
            'CertificateNumberPrefix' => ['nullable', 'string', 'max:40'],
            'IssuedOn' => ['nullable', 'date'],
            'ExpiresOn' => ['nullable', 'date'],
            'OnlyAttended' => ['sometimes', 'boolean'],
            'SendEmail' => ['sometimes', 'boolean'],
        ]);

        $selectedTemplate = TrainingCertificateTemplate::query()
            ->where('Id', (int) $data['CertificateTemplateID'])
            ->where('IsActive', 1)
            ->where(function (Builder $query) use ($session) {
                $query->whereNull('ProgramID')
                    ->orWhere('ProgramID', $session->ProgramID);
            })
            ->first();

        if (! $selectedTemplate) {
            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['certificate' => 'Selected certificate template is not available for this program.'])
                ->withInput();
        }

        $participantsQuery = TrainingSessionParticipant::query()
            ->with(['client:ClientID,Name,Email'])
            ->where('SessionID', $session->Id);

        $onlyAttended = $request->boolean('OnlyAttended', true);
        if ($onlyAttended) {
            $participantsQuery->where('AttendanceStatus', 'Present');
        }

        $totalEligible = (clone $participantsQuery)->count();
        if ($totalEligible === 0) {
            $message = $onlyAttended
                ? 'No attended participants found for bulk certification.'
                : 'No participants found for bulk certification.';

            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 422);
            }

            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['certificate' => $message]);
        }

        $existingClientIds = TrainingCertificate::query()
            ->where('SessionID', $session->Id)
            ->where(function (Builder $query) {
                $query->whereNull('CertificateScope')
                    ->orWhere('CertificateScope', 'Session');
            })
            ->pluck('ClientID')
            ->map(fn ($clientId) => (string) $clientId)
            ->flip();

        $issuedOn = $data['IssuedOn'] ?? now()->toDateString();
        $expiresOn = $data['ExpiresOn'] ?? null;
        if (! $expiresOn && is_numeric($selectedTemplate->DefaultValidityMonths)) {
            $expiresOn = Carbon::parse($issuedOn)->addMonths((int) $selectedTemplate->DefaultValidityMonths)->toDateString();
        }

        $certificationName = $data['CertificationName']
            ?: ($selectedTemplate->Name ?: ($session->program?->Title ?? 'Training Certificate'));
        $issuingBody = $data['IssuingBody'] ?? $selectedTemplate->DefaultIssuingBody;
        $prefix = trim((string) ($data['CertificateNumberPrefix'] ?? ''));
        $sendEmail = $request->boolean('SendEmail', false);
        $preparedTemplateBody = $this->resolveCertificateTemplateBody($selectedTemplate);
        $preparedBackgroundImageDataUri = $this->resolveCertificateBackgroundImageDataUri($selectedTemplate);

        $issuedCount = 0;
        $skippedCount = 0;
        $failedCount = 0;
        $emailsSentCount = 0;
        $emailFailedCount = 0;
        $processedCount = 0;
        $sequence = 1;
        $cancelRequested = false;
        $sessionId = (int) $session->Id;

        $runState = [
            'run_id' => (string) Str::uuid(),
            'session_id' => $sessionId,
            'status' => 'processing',
            'message' => 'Bulk certificate issuance started.',
            'started_at' => now()->toIso8601String(),
            'finished_at' => null,
            'cancel_requested_at' => null,
            'total' => $totalEligible,
            'processed' => 0,
            'issued' => 0,
            'skipped' => 0,
            'failed' => 0,
            'emails_sent' => 0,
            'emails_failed' => 0,
            'only_attended' => $onlyAttended,
            'send_email' => $sendEmail,
            'template_id' => (int) $selectedTemplate->Id,
            'template_name' => (string) $selectedTemplate->Name,
            'created_by' => (int) auth()->id(),
            'updated_at' => null,
        ];

        $this->clearBulkCertificateCancelRequested($sessionId);
        $runState = $this->cacheBulkCertificateState($sessionId, $runState);

        try {
            $participantsQuery
                ->orderBy('Id')
                ->chunkById(25, function (Collection $participants) use (
                    $session,
                    $selectedTemplate,
                    $issuedOn,
                    $expiresOn,
                    $certificationName,
                    $issuingBody,
                    $prefix,
                    $sendEmail,
                    $existingClientIds,
                    $preparedTemplateBody,
                    $preparedBackgroundImageDataUri,
                    $sessionId,
                    &$issuedCount,
                    &$skippedCount,
                    &$failedCount,
                    &$emailsSentCount,
                    &$emailFailedCount,
                    &$processedCount,
                    &$sequence,
                    &$cancelRequested,
                    &$runState
                ) {
                    foreach ($participants as $participant) {
                        if ($this->bulkCertificateCancelRequested($sessionId)) {
                            $cancelRequested = true;

                            break;
                        }

                        $clientId = (string) $participant->ClientID;
                        if ($existingClientIds->has($clientId)) {
                            $skippedCount++;
                            $processedCount++;
                            $runState['message'] = 'Processing certificates...';
                            $runState['processed'] = $processedCount;
                            $runState['issued'] = $issuedCount;
                            $runState['skipped'] = $skippedCount;
                            $runState['failed'] = $failedCount;
                            $runState['emails_sent'] = $emailsSentCount;
                            $runState['emails_failed'] = $emailFailedCount;
                            $runState = $this->cacheBulkCertificateState($sessionId, $runState);

                            continue;
                        }

                        $certificateNumber = $this->buildBulkCertificateNumber(
                            prefix: $prefix !== '' ? $prefix : null,
                            session: $session,
                            participant: $participant,
                            issuedOn: $issuedOn,
                            sequence: $sequence
                        );

                        try {
                            $documentId = $this->generateCertificateDocument(
                                session: $session,
                                participant: $participant,
                                selectedTemplate: $selectedTemplate,
                                certificationName: $certificationName,
                                issuingBody: $issuingBody,
                                certificateNumber: $certificateNumber,
                                issuedOn: $issuedOn,
                                expiresOn: $expiresOn,
                                preparedTemplateBody: $preparedTemplateBody,
                                preparedBackgroundImageDataUri: $preparedBackgroundImageDataUri
                            );

                            $certificate = TrainingCertificate::create([
                                'SessionID' => $session->Id,
                                'ProgramID' => (int) $session->ProgramID,
                                'CertificateScope' => 'Session',
                                'TemplateID' => $selectedTemplate->Id,
                                'ClientID' => $participant->ClientID,
                                'CertificationName' => $certificationName,
                                'IssuingBody' => $issuingBody,
                                'CertificateNumber' => $certificateNumber,
                                'IssuedOn' => $issuedOn,
                                'ExpiresOn' => $expiresOn,
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

                            $existingClientIds->put($clientId, true);
                            $issuedCount++;
                            $sequence++;

                            if ($sendEmail) {
                                if ($this->sendCertificateEmail($session, $participant, $certificate)) {
                                    $emailsSentCount++;
                                } else {
                                    $emailFailedCount++;
                                }
                            }

                            unset($certificate, $documentId);
                        } catch (\Throwable $e) {
                            report($e);
                            $failedCount++;
                        }

                        $processedCount++;
                        $runState['message'] = 'Processing certificates...';
                        $runState['processed'] = $processedCount;
                        $runState['issued'] = $issuedCount;
                        $runState['skipped'] = $skippedCount;
                        $runState['failed'] = $failedCount;
                        $runState['emails_sent'] = $emailsSentCount;
                        $runState['emails_failed'] = $emailFailedCount;
                        $runState = $this->cacheBulkCertificateState($sessionId, $runState);
                    }

                    if ($cancelRequested) {
                        $runState['status'] = 'cancelling';
                        $runState['message'] = 'Stop requested. Finishing current batch...';
                        $runState['processed'] = $processedCount;
                        $runState['issued'] = $issuedCount;
                        $runState['skipped'] = $skippedCount;
                        $runState['failed'] = $failedCount;
                        $runState['emails_sent'] = $emailsSentCount;
                        $runState['emails_failed'] = $emailFailedCount;
                        $runState = $this->cacheBulkCertificateState($sessionId, $runState);

                        return false;
                    }

                    if (function_exists('gc_collect_cycles')) {
                        gc_collect_cycles();
                    }
                }, 'Id');
        } catch (\Throwable $e) {
            report($e);
            $runState['status'] = 'failed';
            $runState['message'] = 'Bulk certificate issuance failed before completion.';
            $runState['finished_at'] = now()->toIso8601String();
            $runState['processed'] = $processedCount;
            $runState['issued'] = $issuedCount;
            $runState['skipped'] = $skippedCount;
            $runState['failed'] = $failedCount;
            $runState['emails_sent'] = $emailsSentCount;
            $runState['emails_failed'] = $emailFailedCount;
            $runState = $this->cacheBulkCertificateState($sessionId, $runState);
            $this->clearBulkCertificateCancelRequested($sessionId);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Bulk certificate issuance failed.',
                    'run' => $runState,
                ], 500);
            }

            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['certificate' => 'Bulk certificate issuance failed.']);
        }

        $parts = [];
        $parts[] = $issuedCount . ' certificate(s) issued';
        if ($skippedCount > 0) {
            $parts[] = $skippedCount . ' skipped (already issued)';
        }
        if ($failedCount > 0) {
            $parts[] = $failedCount . ' failed';
        }
        if ($sendEmail) {
            $parts[] = $emailsSentCount . ' email(s) sent';
            if ($emailFailedCount > 0) {
                $parts[] = $emailFailedCount . ' email(s) failed';
            }
        }

        $summary = ucfirst(implode(', ', $parts)) . '.';

        if ($cancelRequested) {
            $runState['status'] = 'cancelled';
            $runState['message'] = 'Bulk certificate issuance was stopped.';
            $runState['cancel_requested_at'] = $runState['cancel_requested_at'] ?: now()->toIso8601String();
        } elseif ($failedCount > 0) {
            $runState['status'] = 'completed_with_errors';
            $runState['message'] = 'Bulk certificate issuance completed with some failures.';
        } else {
            $runState['status'] = 'completed';
            $runState['message'] = 'Bulk certificate issuance completed.';
        }

        $runState['finished_at'] = now()->toIso8601String();
        $runState['processed'] = $processedCount;
        $runState['issued'] = $issuedCount;
        $runState['skipped'] = $skippedCount;
        $runState['failed'] = $failedCount;
        $runState['emails_sent'] = $emailsSentCount;
        $runState['emails_failed'] = $emailFailedCount;
        $runState = $this->cacheBulkCertificateState($sessionId, $runState);
        $this->clearBulkCertificateCancelRequested($sessionId);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $summary,
                'run' => $runState,
            ]);
        }

        return redirect()->route('crm.training.sessions.show', $id)
            ->with('success', $summary);
    }

    public function bulkCertificateStatus($id): JsonResponse
    {
        $session = TrainingSession::findOrFail($id);
        $state = $this->getBulkCertificateState((int) $session->Id);

        return response()->json([
            'run' => $state,
        ]);
    }

    public function cancelBulkIssueCertificates(Request $request, $id)
    {
        $session = TrainingSession::findOrFail($id);
        $sessionId = (int) $session->Id;
        $state = $this->getBulkCertificateState($sessionId);
        $status = (string) ($state['status'] ?? 'idle');

        if (! in_array($status, ['processing', 'cancelling'], true)) {
            $message = 'No active bulk certificate issuance is currently running.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'run' => $state,
                ], 409);
            }

            return redirect()->route('crm.training.sessions.show', $id)
                ->withErrors(['certificate' => $message]);
        }

        $this->markBulkCertificateCancelRequested($sessionId);
        $state['status'] = 'cancelling';
        $state['message'] = 'Stop requested. Finishing current record and stopping...';
        $state['cancel_requested_at'] = now()->toIso8601String();
        $state = $this->cacheBulkCertificateState($sessionId, $state);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Stop requested.',
                'run' => $state,
            ]);
        }

        return redirect()->route('crm.training.sessions.show', $id)
            ->with('success', 'Stop requested. Bulk certificate issuance will stop shortly.');
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
            ->pluck('ClientID')
            ->map(fn ($id) => (string)$id)
            ->toArray();

        $now = now();
        foreach ($data['Feedback'] ?? [] as $clientId => $row) {
            $clientId = trim((string)$clientId);
            if ($clientId === '' || ! in_array($clientId, $participantIds, true)) {
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
                'ClientID' => $clientId,
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

        return redirect()->route('crm.training.sessions.feedback.form', $session->Id)
            ->with('success', 'Feedback saved.');
    }

    private function attachSessionDocuments(TrainingSession $session, Request $request): void
    {
        if ($request->hasFile('AgendaFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::CRM),
                $request->file('AgendaFile'),
                auth()->user()
            )->document;
            $session->update(['AgendaDocumentId' => $document->Id]);
        }

        if ($request->hasFile('MaterialsFile')) {
            $document = DocumentService::createUpload(
                RepositoryService::module(ModulesEnum::CRM),
                $request->file('MaterialsFile'),
                auth()->user()
            )->document;
            $session->update(['MaterialsDocumentId' => $document->Id]);
        }
    }

    private function syncParticipants(TrainingSession $session, Collection $validClientIds): int
    {
        if ($validClientIds->isEmpty()) {
            return 0;
        }

        $existing = [];
        foreach ($validClientIds->chunk(1000) as $chunk) {
            $chunkExisting = TrainingSessionParticipant::query()
                ->where('SessionID', $session->Id)
                ->whereIn('ClientID', $chunk->all())
                ->pluck('ClientID')
                ->map(fn ($id) => (string) $id)
                ->toArray();

            if (! empty($chunkExisting)) {
                $existing = array_merge($existing, $chunkExisting);
            }
        }
        $existing = array_values(array_unique($existing));

        $newClientIds = $validClientIds->reject(fn ($clientId) => in_array($clientId, $existing, true))->values();
        if ($newClientIds->isEmpty()) {
            return 0;
        }

        $existingCount = TrainingSessionParticipant::where('SessionID', $session->Id)->count();
        if (is_numeric($session->MaxParticipants)) {
            $maxParticipants = (int) $session->MaxParticipants;
            if (($existingCount + $newClientIds->count()) > $maxParticipants) {
                $remaining = max($maxParticipants - $existingCount, 0);

                throw ValidationException::withMessages([
                    'ClientIDs' => $remaining > 0
                        ? 'Max participants is ' . $maxParticipants . '. You can only add ' . $remaining . ' more participant(s).'
                        : 'Max participants is ' . $maxParticipants . '. This session is already full.',
                ]);
            }
        }

        $now = now();
        $insert = [];
        foreach ($newClientIds as $clientId) {
            $insert[] = [
                'SessionID' => $session->Id,
                'ClientID' => $clientId,
                'EnrollmentMethod' => 'CRM',
                'Status' => 'Nominated',
                'EnrolledOn' => $now,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
            ];
        }

        if (! empty($insert)) {
            foreach (array_chunk($insert, 250) as $batch) {
                TrainingSessionParticipant::insert($batch);
            }
        }

        return count($insert);
    }

    private function validateProgramParticipantSelection(int $programId, Collection $requestedClientIds): Collection
    {
        if ($requestedClientIds->isEmpty()) {
            return collect();
        }

        $programClientIds = $this->getProgramTargetClientIds($programId);
        if ($programClientIds->isEmpty()) {
            throw ValidationException::withMessages([
                'ClientIDs' => 'This program has no registered participants. Add participants to the program first.',
            ]);
        }

        $outsideProgramIds = $requestedClientIds->diff($programClientIds)->values();
        if ($outsideProgramIds->isNotEmpty()) {
            throw ValidationException::withMessages([
                'ClientIDs' => 'Selected clients must be registered participants of the selected program.',
            ]);
        }

        $validClientIds = collect();
        foreach ($requestedClientIds->chunk(1000) as $chunk) {
            $validClientIds = $validClientIds->merge(
                Client::query()
                    ->whereIn('ClientID', $chunk->all())
                    ->pluck('ClientID')
                    ->map(fn ($id) => (string) $id)
                    ->values()
            );
        }

        return $validClientIds->unique()->values();
    }

    private function getProgramTargetClientIds(int $programId): Collection
    {
        if ($programId <= 0) {
            return collect();
        }

        return TrainingProgramTarget::query()
            ->where('ProgramID', $programId)
            ->where('TargetType', 'Client')
            ->pluck('TargetID')
            ->map(fn ($id) => trim((string) $id))
            ->filter()
            ->unique()
            ->values();
    }

    private function loadClientsByIds(Collection $clientIds): Collection
    {
        if ($clientIds->isEmpty()) {
            return collect();
        }

        return Client::query()
            ->with([
                'individual:ClientID,PassportNo',
                'corporate:ClientID,CertificateNo',
            ])
            ->whereIn('ClientID', $clientIds->all())
            ->orderBy('Name')
            ->get(['ClientID', 'Name']);
    }

    private function canMarkAttendance(TrainingSession $session): bool
    {
        return ! in_array($session->Status, ['Cancelled'], true);
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

    private function findRecentDuplicateSession(array $data): ?TrainingSession
    {
        $query = TrainingSession::query()
            ->where('ProgramID', $data['ProgramID'])
            ->where('CreatedBy', $data['CreatedBy'])
            ->where('CreatedOn', '>=', now()->subSeconds(15));

        if (! empty($data['SessionCode'])) {
            $query->where('SessionCode', $data['SessionCode']);
        } elseif (! empty($data['Title'])) {
            $query->where('Title', $data['Title']);
        }

        if (! empty($data['StartDate'])) {
            $query->whereDate('StartDate', $data['StartDate']);
        } else {
            $query->whereNull('StartDate');
        }

        if (! empty($data['StartTime'])) {
            $query->where('StartTime', $data['StartTime']);
        } else {
            $query->whereNull('StartTime');
        }

        if (! empty($data['TrainerID'])) {
            $query->where('TrainerID', $data['TrainerID']);
        }

        return $query->orderByDesc('Id')->first();
    }

    private function generateCertificateDocument(
        TrainingSession $session,
        TrainingSessionParticipant $participant,
        ?TrainingCertificateTemplate $selectedTemplate,
        string $certificationName,
        ?string $issuingBody,
        ?string $certificateNumber,
        string $issuedOn,
        ?string $expiresOn,
        ?string $preparedTemplateBody = null,
        ?string $preparedBackgroundImageDataUri = null
    ): int {
        $participant->loadMissing('client');

        $participantName = trim((string) ($participant->client?->Name ?? $participant->ClientID));
        $programName = trim((string) ($session->program?->Title ?? $certificationName));
        $sessionTitle = trim((string) ($session->Title ?: ($session->SessionCode ?: ('Session #' . $session->Id))));
        $issuedOnDate = Carbon::parse($issuedOn);
        $expiresOnDate = $expiresOn ? Carbon::parse($expiresOn) : null;

        $tokenValues = [
            '{{participant_name}}' => $participantName,
            '{{client_id}}' => (string) $participant->ClientID,
            '{{program_name}}' => $programName,
            '{{session_title}}' => $sessionTitle,
            '{{issue_date}}' => $issuedOnDate->format('jS \d\a\y \o\f F, Y'),
            '{{expiry_date}}' => $expiresOnDate?->format('jS \d\a\y \o\f F, Y') ?? '',
            '{{certificate_number}}' => $certificateNumber ?? '',
            '{{issuing_body}}' => $issuingBody ?? '',
        ];

        $templateBody = $preparedTemplateBody ?? $this->resolveCertificateTemplateBody($selectedTemplate);
        $renderedTemplateBody = $this->renderCertificateTemplateText($templateBody, $tokenValues);
        $backgroundImageDataUri = $preparedBackgroundImageDataUri ?? $this->resolveCertificateBackgroundImageDataUri($selectedTemplate);

        $pdf = Pdf::loadView('crm.training.certificates.print', [
            'participantName' => $participantName,
            'programName' => $programName,
            'sessionTitle' => $sessionTitle,
            'certificationName' => $certificationName,
            'certificateNumber' => $certificateNumber,
            'issuingBody' => $issuingBody ?: config('app.name'),
            'issuedOnLabel' => $issuedOnDate->format('jS \d\a\y \o\f F, Y'),
            'expiresOnLabel' => $expiresOnDate?->format('jS \d\a\y \o\f F, Y'),
            'sessionDateLabel' => $session->StartDate ? Carbon::parse($session->StartDate)->format('jS \d\a\y \o\f F, Y') : null,
            'renderedTemplateBody' => $renderedTemplateBody,
            'backgroundImageDataUri' => $backgroundImageDataUri,
        ])->setPaper('a4', 'landscape');
        $pdfOutput = $pdf->output();
        unset($pdf);

        $document = DocumentService::createContent(
            RepositoryService::module(ModulesEnum::CRM),
            ExtensionsEnum::Pdf,
            $this->buildCertificateFileName($participantName, $programName, $issuedOnDate),
            $pdfOutput,
            auth()->user()
        )->document;
        unset($pdfOutput);

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        return (int) $document->Id;
    }

    private function resolveCertificateTemplateBody(?TrainingCertificateTemplate $selectedTemplate): string
    {
        $templateBody = trim((string) ($selectedTemplate?->TemplateBody ?? ''));
        if ($templateBody !== '') {
            return $templateBody;
        }

        return 'This certifies that {{participant_name}} has successfully completed {{program_name}} on {{issue_date}}.';
    }

    private function renderCertificateTemplateText(string $templateBody, array $tokenValues): string
    {
        return strtr($templateBody, $tokenValues);
    }

    private function resolveCertificateBackgroundImageDataUri(?TrainingCertificateTemplate $selectedTemplate): ?string
    {
        if (! $selectedTemplate) {
            return null;
        }

        $selectedTemplate->loadMissing('document.current');
        $document = $selectedTemplate->document;
        if ($document && is_string($document->MimeType) && str_starts_with($document->MimeType, 'image/')) {
            try {
                $encoded = (new DocumentService($document))->getFileContent();
                if ($encoded !== '') {
                    return 'data:' . $document->MimeType . ';base64,' . $encoded;
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $backgroundImagePath = trim((string) ($selectedTemplate->BackgroundImagePath ?? ''));
        if ($backgroundImagePath === '') {
            return null;
        }

        $normalizedPath = str_replace('\\', '/', ltrim($backgroundImagePath, '/\\'));
        if (! preg_match('/^assets\/certificates\/templates\/[A-Za-z0-9._-]+$/', $normalizedPath)) {
            return null;
        }

        $fullPath = public_path($normalizedPath);
        if (! is_file($fullPath)) {
            return null;
        }

        $content = @file_get_contents($fullPath);
        if ($content === false || $content === '') {
            return null;
        }

        $mimeType = @mime_content_type($fullPath) ?: 'image/svg+xml';

        return 'data:' . $mimeType . ';base64,' . base64_encode($content);
    }

    private function buildBulkCertificateNumber(
        ?string $prefix,
        TrainingSession $session,
        TrainingSessionParticipant $participant,
        string $issuedOn,
        int $sequence
    ): string {
        $normalizedPrefix = trim((string) $prefix);
        if ($normalizedPrefix === '') {
            $normalizedPrefix = 'CERT-' . $session->Id . '-' . Carbon::parse($issuedOn)->format('Ymd');
        }

        return strtoupper($normalizedPrefix) . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT) . '-' . $participant->ClientID;
    }

    private function sendCertificateEmail(
        TrainingSession $session,
        TrainingSessionParticipant $participant,
        TrainingCertificate $certificate
    ): bool {
        try {
            $participant->loadMissing('client');
            $client = $participant->client;
            if (! $client) {
                return false;
            }

            $email = trim((string) ($client->Email ?? ''));
            if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $document = $certificate->document ?: ($certificate->DocumentId ? Document::find($certificate->DocumentId) : null);
            if (! $document) {
                return false;
            }

            $fileContent = (new DocumentService($document))->getFileContent(false);
            if ($fileContent === '') {
                return false;
            }

            $participantName = trim((string) ($client->Name ?? $participant->ClientID));
            $programName = trim((string) ($session->program?->Title ?? $certificate->CertificationName));
            $subject = 'Training Certificate - ' . ($certificate->CertificationName ?: $programName);
            $body = '<p>Dear ' . e($participantName) . ',</p>'
                . '<p>Your training certificate for <strong>' . e($programName) . '</strong> is attached.</p>'
                . '<p>Certificate Number: <strong>' . e((string) ($certificate->CertificateNumber ?? '-')) . '</strong></p>'
                . '<p>Issued On: <strong>' . e($certificate->IssuedOn?->format('Y-m-d') ?? '-') . '</strong></p>'
                . '<p>Regards,<br>' . e(config('org.name', config('app.name'))) . '</p>';

            $service = CRMEmailService::createClient(
                $client,
                $email,
                $subject,
                $body,
                auth()->user()
            );

            $service->addAttachmentContent(
                $fileContent,
                $document->MimeType ?: ExtensionsEnum::Pdf->getMimeType(),
                $document->Name ?: ('training-certificate-' . $participant->ClientID . '.pdf'),
                auth()->user()
            );

            $service->send();

            return true;
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }

    private function bulkCertificateStateCacheKey(int $sessionId): string
    {
        return 'crm:training:session:' . $sessionId . ':bulk-certificate:state';
    }

    private function bulkCertificateCancelCacheKey(int $sessionId): string
    {
        return 'crm:training:session:' . $sessionId . ':bulk-certificate:cancel';
    }

    private function bulkCertificateDefaultState(): array
    {
        return [
            'run_id' => null,
            'session_id' => null,
            'status' => 'idle',
            'message' => '',
            'started_at' => null,
            'finished_at' => null,
            'cancel_requested_at' => null,
            'total' => 0,
            'processed' => 0,
            'issued' => 0,
            'skipped' => 0,
            'failed' => 0,
            'emails_sent' => 0,
            'emails_failed' => 0,
            'only_attended' => true,
            'send_email' => false,
            'template_id' => null,
            'template_name' => null,
            'created_by' => null,
            'updated_at' => null,
        ];
    }

    private function getBulkCertificateState(int $sessionId): array
    {
        $cached = Cache::get($this->bulkCertificateStateCacheKey($sessionId));
        if (! is_array($cached)) {
            $default = $this->bulkCertificateDefaultState();
            $default['session_id'] = $sessionId;

            return $default;
        }

        return array_merge($this->bulkCertificateDefaultState(), $cached);
    }

    private function cacheBulkCertificateState(int $sessionId, array $state): array
    {
        $state = array_merge($this->bulkCertificateDefaultState(), $state);
        $state['session_id'] = $sessionId;
        $state['updated_at'] = now()->toIso8601String();

        Cache::put(
            $this->bulkCertificateStateCacheKey($sessionId),
            $state,
            now()->addHours(24)
        );

        return $state;
    }

    private function markBulkCertificateCancelRequested(int $sessionId): void
    {
        Cache::put($this->bulkCertificateCancelCacheKey($sessionId), true, now()->addHours(24));
    }

    private function clearBulkCertificateCancelRequested(int $sessionId): void
    {
        Cache::forget($this->bulkCertificateCancelCacheKey($sessionId));
    }

    private function bulkCertificateCancelRequested(int $sessionId): bool
    {
        return (bool) Cache::get($this->bulkCertificateCancelCacheKey($sessionId), false);
    }

    private function buildCertificateFileName(string $participantName, string $programName, Carbon $issuedOn): string
    {
        $programSlug = Str::slug($programName);
        $participantSlug = Str::slug($participantName);

        if ($programSlug === '') {
            $programSlug = 'program';
        }
        if ($participantSlug === '') {
            $participantSlug = 'participant';
        }

        $base = 'training-certificate-' . $programSlug . '-' . $participantSlug . '-' . $issuedOn->format('Ymd');

        return substr($base, 0, 170) . '.pdf';
    }
}
