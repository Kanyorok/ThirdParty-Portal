<?php

namespace App\Http\Controllers\CRM\Training;

use App\Enums\Core\ExtensionsEnum;
use App\Enums\Core\ModulesEnum;
use App\Enums\Core\VisibilityEnum;
use App\Enums\MarketingListEnum;
use App\Exceptions\ErroredException;
use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\CRM\MarketingList;
use App\Models\CRM\Training\TrainingCategory;
use App\Models\CRM\Training\TrainingCertificate;
use App\Models\CRM\Training\TrainingCertificateTemplate;
use App\Models\CRM\Training\TrainingProgram;
use App\Models\CRM\Training\TrainingProgramTarget;
use App\Models\CRM\Training\TrainingSession;
use App\Models\CRM\Training\TrainingSessionParticipant;
use App\Models\DMS\Document;
use App\Services\CRMEmailService;
use App\Services\DMS\DocumentService;
use App\Services\DMS\RepositoryService;
use App\Services\Marketing\DynamicListService;
use App\Services\Marketing\ListService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ClientTrainingProgramController extends Controller
{
    public function index(Request $request)
    {
        $query = TrainingProgram::with('category')
            ->withCount([
                'targets as target_clients_count' => function ($targetQuery) {
                    $targetQuery->where('TargetType', 'Client');
                },
            ]);

        if ($request->filled('category_id')) {
            $query->where('CategoryID', $request->category_id);
        }
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('mandatory')) {
            $query->where('IsMandatory', (int) $request->mandatory);
        }

        $programs = $query->orderBy('Title')->paginate(30);
        $categories = TrainingCategory::orderBy('Name')->get();
        $statusList = ['Active', 'Inactive'];

        return view('crm.training.client-programs.index', compact('programs', 'categories', 'statusList'));
    }

    public function create()
    {
        $categories = TrainingCategory::orderBy('Name')->get();
        $deliveryModes = ['Classroom', 'Online', 'Blended'];

        return view('crm.training.client-programs.create', compact('categories', 'deliveryModes'));
    }

    public function store(Request $request)
    {
        $data = $this->validateProgram($request);
        $data['CreatedBy'] = auth()->id();
        $data['CreatedOn'] = now();

        $program = TrainingProgram::create($data);

        return redirect()->route('crm.training.programs.participants', $program->Id)
            ->with('success', 'Training program created. Add participants below.');
    }

    public function show($id)
    {
        $program = TrainingProgram::with(['category', 'targets', 'sessions'])->findOrFail($id);
        $targetClients = $this->loadClientsByIds($this->getTargetClientIds($program));

        return view('crm.training.client-programs.show', compact('program', 'targetClients'));
    }

    public function edit($id)
    {
        $program = TrainingProgram::with('targets')->findOrFail($id);
        $categories = TrainingCategory::orderBy('Name')->get();
        $deliveryModes = ['Classroom', 'Online', 'Blended'];
        $targetClientCount = $this->getTargetClientIds($program)->count();

        return view('crm.training.client-programs.edit', compact(
            'program',
            'categories',
            'deliveryModes',
            'targetClientCount'
        ));
    }

    public function update(Request $request, $id)
    {
        $program = TrainingProgram::findOrFail($id);

        $data = $this->validateProgram($request, $program);
        $data['ModifiedBy'] = auth()->id();
        $data['ModifiedOn'] = now();

        $program->update($data);

        return redirect()->route('crm.training.programs.participants', $program->Id)
            ->with('success', 'Training program updated.');
    }

    public function participants(Request $request, $id)
    {
        $program = TrainingProgram::with('category')->findOrFail($id);
        $targetClientIds = $this->getTargetClientIds($program);
        $totalParticipants = $targetClientIds->count();
        $marketingLists = $this->getAccessibleClientMarketingLists();

        $participantFilters = [
            'name' => trim((string) $request->query('participant_name', '')),
            'client_id' => trim((string) $request->query('participant_client_id', '')),
            'id_number' => trim((string) $request->query('participant_id_number', '')),
        ];
        $hasParticipantFilters = $participantFilters['name'] !== ''
            || $participantFilters['client_id'] !== ''
            || $participantFilters['id_number'] !== '';

        $selectedParticipantsQuery = Client::query()
            ->with([
                'individual:ClientID,PassportNo',
                'corporate:ClientID,CertificateNo',
            ])
            ->select(['ClientID', 'Name'])
            ->orderBy('Name');

        if ($targetClientIds->isEmpty()) {
            $selectedParticipantsQuery->whereRaw('1 = 0');
        } else {
            $selectedParticipantsQuery->whereIn('ClientID', $targetClientIds->all());
        }

        if ($participantFilters['name'] !== '') {
            $selectedParticipantsQuery->where('Name', 'like', '%' . $participantFilters['name'] . '%');
        }
        if ($participantFilters['client_id'] !== '') {
            $selectedParticipantsQuery->where('ClientID', 'like', '%' . $participantFilters['client_id'] . '%');
        }
        if ($participantFilters['id_number'] !== '') {
            $selectedParticipantsQuery->where(function (Builder $idQuery) use ($participantFilters) {
                $idQuery->whereHas('individual', function (Builder $individualQuery) use ($participantFilters) {
                    $individualQuery->where('PassportNo', 'like', '%' . $participantFilters['id_number'] . '%');
                })->orWhereHas('corporate', function (Builder $corporateQuery) use ($participantFilters) {
                    $corporateQuery->where('CertificateNo', 'like', '%' . $participantFilters['id_number'] . '%');
                });
            });
        }

        $selectedParticipants = $selectedParticipantsQuery
            ->paginate(20)
            ->withQueryString();

        $previewListId = (int) $request->query('preview_marketing_list_id', 0);
        $listPreview = null;
        $previewClients = collect();

        if ($previewListId > 0) {
            $previewList = $this->findAccessibleClientMarketingList($previewListId);
            if (!$previewList) {
                return redirect()->route('crm.training.programs.participants', $program->Id)
                    ->withErrors('Marketing list not found or not accessible.');
            }

            $rawClientIds = $this->resolveClientIdsFromMarketingList($previewList);
            $validClientIds = $this->resolveValidClientIds($rawClientIds);
            $alreadySelectedClientIds = $validClientIds->intersect($targetClientIds)->values();
            $eligibleClientIds = $validClientIds->diff($targetClientIds)->values();

            $listPreview = [
                'MarketingListID' => $previewList->MarketingListID,
                'Label' => $previewList->Label,
                'Type' => $previewList->Type?->name ?? 'Unknown',
                'SourceLabel' => (new ListService($previewList))->source(),
                'raw_count' => $rawClientIds->count(),
                'valid_count' => $validClientIds->count(),
                'already_selected_count' => $alreadySelectedClientIds->count(),
                'eligible_count' => $eligibleClientIds->count(),
            ];

            if ($eligibleClientIds->isNotEmpty()) {
                $previewClients = $this->loadClientsByIds($eligibleClientIds->take(20));
            }
        }

        return view('crm.training.client-programs.participants', compact(
            'program',
            'totalParticipants',
            'selectedParticipants',
            'participantFilters',
            'hasParticipantFilters',
            'marketingLists',
            'previewListId',
            'listPreview',
            'previewClients'
        ));
    }

    public function certification(Request $request, $id)
    {
        $program = TrainingProgram::with('category')->findOrFail($id);

        if (!$program->HasCertification) {
            return redirect()->route('crm.training.programs.show', $program->Id)
                ->withErrors('This program has certification disabled.');
        }

        if (($program->CertificateScope ?? 'Session') !== 'Program') {
            return redirect()->route('crm.training.programs.show', $program->Id)
                ->withErrors('This program uses session-level certification. Switch certification scope to Program to use this page.');
        }

        $targetClientIds = $this->getTargetClientIds($program);
        $totalParticipants = $targetClientIds->count();
        $totalSessions = TrainingSession::query()
            ->where('ProgramID', $program->Id)
            ->count();

        $completionConfig = $this->resolveProgramCertificationConfig($program, $totalSessions);
        $attendedCounts = $this->getProgramAttendedSessionCounts((int) $program->Id);

        $eligibleClientIds = $targetClientIds
            ->filter(function (string $clientId) use ($attendedCounts, $completionConfig) {
                $attended = (int) $attendedCounts->get($clientId, 0);
                return $this->isProgramParticipantEligibleForCertification($attended, $completionConfig);
            })
            ->values();
        $eligibleLookup = $eligibleClientIds->flip();

        $existingProgramCertificates = TrainingCertificate::query()
            ->where('ProgramID', $program->Id)
            ->where('CertificateScope', 'Program')
            ->pluck('ClientID')
            ->map(fn ($value) => (string) $value)
            ->flip();

        $issuedEligibleCount = $eligibleClientIds
            ->filter(fn (string $clientId) => $existingProgramCertificates->has($clientId))
            ->count();

        $participantFilters = [
            'name' => trim((string) $request->query('participant_name', '')),
            'client_id' => trim((string) $request->query('participant_client_id', '')),
            'id_number' => trim((string) $request->query('participant_id_number', '')),
        ];

        $participantsQuery = Client::query()
            ->with([
                'individual:ClientID,PassportNo',
                'corporate:ClientID,CertificateNo',
            ])
            ->select(['ClientID', 'Name'])
            ->whereExists(function ($subQuery) use ($program) {
                $subQuery->selectRaw('1')
                    ->from('t_CRMTrainingProgramTargets as tgt')
                    ->whereColumn('tgt.TargetID', 'syn_t_Client.ClientID')
                    ->where('tgt.ProgramID', $program->Id)
                    ->where('tgt.TargetType', 'Client');
            });

        if ($participantFilters['name'] !== '') {
            $participantsQuery->where('Name', 'like', '%' . $participantFilters['name'] . '%');
        }
        if ($participantFilters['client_id'] !== '') {
            $participantsQuery->where('ClientID', 'like', '%' . $participantFilters['client_id'] . '%');
        }
        if ($participantFilters['id_number'] !== '') {
            $participantsQuery->where(function (Builder $idQuery) use ($participantFilters) {
                $idQuery->whereHas('individual', function (Builder $individualQuery) use ($participantFilters) {
                    $individualQuery->where('PassportNo', 'like', '%' . $participantFilters['id_number'] . '%');
                })->orWhereHas('corporate', function (Builder $corporateQuery) use ($participantFilters) {
                    $corporateQuery->where('CertificateNo', 'like', '%' . $participantFilters['id_number'] . '%');
                });
            });
        }

        $participants = $participantsQuery
            ->orderBy('Name')
            ->paginate(20)
            ->withQueryString();

        $participants->getCollection()->transform(function (Client $client) use ($attendedCounts, $completionConfig, $eligibleLookup, $existingProgramCertificates) {
            $clientId = (string) $client->ClientID;
            $attendedSessions = (int) $attendedCounts->get($clientId, 0);
            $requiredSessions = (int) ($completionConfig['required_sessions'] ?? 1);

            $client->AttendedSessions = $attendedSessions;
            $client->RequiredSessions = $requiredSessions;
            $client->IsEligibleForProgramCertificate = $eligibleLookup->has($clientId)
                && $this->isProgramParticipantEligibleForCertification($attendedSessions, $completionConfig);
            $client->ProgramCertificateIssued = $existingProgramCertificates->has($clientId);

            return $client;
        });

        $certificateTemplates = TrainingCertificateTemplate::query()
            ->where('IsActive', 1)
            ->where(function (Builder $query) use ($program) {
                $query->whereNull('ProgramID')
                    ->orWhere('ProgramID', $program->Id);
            })
            ->orderByDesc('IsSample')
            ->orderBy('Name')
            ->get();

        $summary = [
            'total_participants' => $totalParticipants,
            'total_sessions' => $totalSessions,
            'eligible_participants' => $eligibleClientIds->count(),
            'issued_eligible' => $issuedEligibleCount,
            'pending_eligible' => max($eligibleClientIds->count() - $issuedEligibleCount, 0),
        ];

        return view('crm.training.client-programs.certification', compact(
            'program',
            'participants',
            'participantFilters',
            'certificateTemplates',
            'completionConfig',
            'summary'
        ));
    }

    public function issueProgramCertificates(Request $request, $id)
    {
        @ini_set('memory_limit', '512M');
        $program = TrainingProgram::findOrFail($id);

        if (!$program->HasCertification) {
            return redirect()->route('crm.training.programs.certification', $program->Id)
                ->withErrors('This program has certification disabled.');
        }

        if (($program->CertificateScope ?? 'Session') !== 'Program') {
            return redirect()->route('crm.training.programs.certification', $program->Id)
                ->withErrors('This program uses session-level certification.');
        }

        $data = $request->validate([
            'CertificateTemplateID' => ['required', 'integer', 'exists:t_CRMTrainingCertificateTemplates,Id'],
            'CertificationName' => ['nullable', 'string', 'max:200'],
            'IssuingBody' => ['nullable', 'string', 'max:150'],
            'CertificateNumberPrefix' => ['nullable', 'string', 'max:40'],
            'IssuedOn' => ['nullable', 'date'],
            'ExpiresOn' => ['nullable', 'date'],
            'SendEmail' => ['sometimes', 'boolean'],
        ]);

        $selectedTemplate = TrainingCertificateTemplate::query()
            ->where('Id', (int) $data['CertificateTemplateID'])
            ->where('IsActive', 1)
            ->where(function (Builder $query) use ($program) {
                $query->whereNull('ProgramID')
                    ->orWhere('ProgramID', $program->Id);
            })
            ->first();

        if (!$selectedTemplate) {
            return redirect()->route('crm.training.programs.certification', $program->Id)
                ->withErrors('Selected certificate template is not available for this program.')
                ->withInput();
        }

        $totalSessions = TrainingSession::query()
            ->where('ProgramID', $program->Id)
            ->count();

        $completionConfig = $this->resolveProgramCertificationConfig($program, $totalSessions);
        $targetClientIds = $this->getTargetClientIds($program);
        $attendedCounts = $this->getProgramAttendedSessionCounts((int) $program->Id);

        $eligibleClientIds = $targetClientIds
            ->filter(function (string $clientId) use ($attendedCounts, $completionConfig) {
                $attended = (int) $attendedCounts->get($clientId, 0);
                return $this->isProgramParticipantEligibleForCertification($attended, $completionConfig);
            })
            ->values();

        if ($eligibleClientIds->isEmpty()) {
            return redirect()->route('crm.training.programs.certification', $program->Id)
                ->withErrors('No participants meet the program completion rule yet.');
        }

        $existingClientIds = TrainingCertificate::query()
            ->where('ProgramID', $program->Id)
            ->where('CertificateScope', 'Program')
            ->pluck('ClientID')
            ->map(fn ($value) => (string) $value)
            ->flip();

        $issuedOn = $data['IssuedOn'] ?? now()->toDateString();
        $expiresOn = $data['ExpiresOn'] ?? null;
        if (!$expiresOn && is_numeric($selectedTemplate->DefaultValidityMonths)) {
            $expiresOn = Carbon::parse($issuedOn)->addMonths((int) $selectedTemplate->DefaultValidityMonths)->toDateString();
        }

        $certificationName = $data['CertificationName']
            ?: ($selectedTemplate->Name ?: ($program->Title ?: 'Training Certificate'));
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
        $sequence = 1;

        foreach ($eligibleClientIds->chunk(100) as $chunkClientIds) {
            $clients = Client::query()
                ->whereIn('ClientID', $chunkClientIds->all())
                ->get(['ClientID', 'Name', 'Email'])
                ->keyBy('ClientID');

            foreach ($chunkClientIds as $clientId) {
                $clientId = (string) $clientId;
                if ($existingClientIds->has($clientId)) {
                    $skippedCount++;
                    continue;
                }

                $client = $clients->get($clientId);
                if (!$client) {
                    $failedCount++;
                    continue;
                }

                $certificateNumber = $this->buildProgramCertificateNumber(
                    prefix: $prefix !== '' ? $prefix : null,
                    program: $program,
                    clientId: $clientId,
                    issuedOn: $issuedOn,
                    sequence: $sequence
                );

                try {
                    $documentId = $this->generateProgramCertificateDocument(
                        program: $program,
                        client: $client,
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
                        'SessionID' => null,
                        'ProgramID' => $program->Id,
                        'CertificateScope' => 'Program',
                        'TemplateID' => $selectedTemplate->Id,
                        'ClientID' => $clientId,
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

                    $issuedCount++;
                    $sequence++;
                    $existingClientIds->put($clientId, true);

                    if ($sendEmail) {
                        if ($this->sendProgramCertificateEmail($program, $client, $certificate)) {
                            $emailsSentCount++;
                        } else {
                            $emailFailedCount++;
                        }
                    }
                } catch (\Throwable $e) {
                    report($e);
                    $failedCount++;
                }
            }

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        }

        if ($issuedCount === 0 && $failedCount === 0) {
            return redirect()->route('crm.training.programs.certification', $program->Id)
                ->withErrors('No certificates were issued. Eligible participants may already have certificates.');
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

        return redirect()->route('crm.training.programs.certification', $program->Id)
            ->with('success', ucfirst(implode(', ', $parts)) . '.');
    }

    public function addParticipants(Request $request, $id)
    {
        $program = TrainingProgram::findOrFail($id);

        $data = $request->validate([
            'TargetClients' => ['required', 'array', 'min:1'],
            'TargetClients.*' => ['string', 'max:50'],
        ]);

        $validClientIds = $this->resolveValidClientIds(collect($data['TargetClients']));

        if ($validClientIds->isEmpty()) {
            return back()->withErrors('Selected clients were not found.');
        }

        $inserted = $this->appendParticipants($program, $validClientIds);
        if ($inserted === 0) {
            return back()->with('success', 'Selected clients are already in the participant list.');
        }

        return back()->with('success', $inserted . ' participant(s) added.');
    }

    public function addParticipantsFromMarketingList(Request $request, $id)
    {
        $program = TrainingProgram::findOrFail($id);

        $data = $request->validate([
            'MarketingListID' => ['required', 'integer', 'exists:t_MarketingLists,MarketingListID'],
        ]);

        $marketingList = $this->findAccessibleClientMarketingList((int) $data['MarketingListID']);
        if (!$marketingList) {
            return back()->withErrors('Marketing list not found or not accessible.');
        }

        $listClientIds = $this->resolveClientIdsFromMarketingList($marketingList);
        $validClientIds = $this->resolveValidClientIds($listClientIds);

        if ($validClientIds->isEmpty()) {
            return back()->withErrors('No valid clients were found in the selected marketing list.');
        }

        $inserted = $this->appendParticipants($program, $validClientIds);
        if ($inserted === 0) {
            return back()->with('success', 'All eligible clients from the selected marketing list are already participants.');
        }

        return back()->with('success', $inserted . ' participant(s) added from marketing list: ' . $marketingList->Label . '.');
    }

    public function removeParticipant($id, $clientId)
    {
        $program = TrainingProgram::findOrFail($id);
        $clientId = trim((string) $clientId);

        if ($clientId === '') {
            return back()->withErrors('Invalid client identifier.');
        }

        $deleted = TrainingProgramTarget::query()
            ->where('ProgramID', $program->Id)
            ->where('TargetType', 'Client')
            ->where('TargetID', $clientId)
            ->delete();

        if ($deleted === 0) {
            return back()->withErrors('Participant not found.');
        }

        return back()->with('success', 'Participant removed.');
    }

    private function validateProgram(Request $request, ?TrainingProgram $program = null): array
    {
        $codeValidation = $program
            ? Rule::unique('t_CRMTrainingPrograms', 'Code')->ignore($program->Id, 'Id')
            : Rule::unique('t_CRMTrainingPrograms', 'Code');

        $data = $request->validate([
            'Code' => ['required', 'string', 'max:30', $codeValidation],
            'Title' => ['required', 'string', 'max:200'],
            'CategoryID' => ['nullable', 'exists:t_CRMTrainingCategories,Id'],
            'DeliveryMode' => ['nullable', 'string', 'max:50'],
            'DurationHours' => ['nullable', 'numeric', 'min:0'],
            'Objectives' => ['nullable', 'string'],
            'TargetAudience' => ['nullable', 'string'],
            'BudgetedCost' => ['nullable', 'numeric', 'min:0'],
            'ActualCost' => ['nullable', 'numeric', 'min:0'],
            'IsMandatory' => ['sometimes', 'boolean'],
            'HasCertification' => ['sometimes', 'boolean'],
            'CertificateScope' => ['nullable', Rule::in(['Session', 'Program'])],
            'CertificationCompletionRule' => ['nullable', Rule::in(['AnySession', 'MinSessions', 'AllSessions'])],
            'CertificationMinimumSessions' => ['nullable', 'integer', 'min:1', 'max:999'],
            'Status' => ['nullable', 'string', 'max:30'],
        ]);

        $data['IsMandatory'] = $request->boolean('IsMandatory', false);
        $data['HasCertification'] = $request->boolean('HasCertification', false);
        $data['Status'] = $data['Status'] ?? ($program?->Status ?? 'Active');

        if (!$data['HasCertification']) {
            $data['CertificateScope'] = 'Session';
            $data['CertificationCompletionRule'] = null;
            $data['CertificationMinimumSessions'] = null;

            return $data;
        }

        $scope = in_array((string) ($data['CertificateScope'] ?? 'Session'), ['Session', 'Program'], true)
            ? (string) ($data['CertificateScope'] ?? 'Session')
            : 'Session';
        $data['CertificateScope'] = $scope;

        if ($scope === 'Program') {
            $rule = in_array((string) ($data['CertificationCompletionRule'] ?? 'AnySession'), ['AnySession', 'MinSessions', 'AllSessions'], true)
                ? (string) ($data['CertificationCompletionRule'] ?? 'AnySession')
                : 'AnySession';

            $data['CertificationCompletionRule'] = $rule;
            $data['CertificationMinimumSessions'] = $rule === 'MinSessions'
                ? max((int) ($data['CertificationMinimumSessions'] ?? 1), 1)
                : null;
        } else {
            $data['CertificationCompletionRule'] = null;
            $data['CertificationMinimumSessions'] = null;
        }

        return $data;
    }

    private function resolveProgramCertificationConfig(TrainingProgram $program, int $totalSessions): array
    {
        $scope = in_array((string) ($program->CertificateScope ?? 'Session'), ['Session', 'Program'], true)
            ? (string) ($program->CertificateScope ?? 'Session')
            : 'Session';
        $rule = in_array((string) ($program->CertificationCompletionRule ?? 'AnySession'), ['AnySession', 'MinSessions', 'AllSessions'], true)
            ? (string) ($program->CertificationCompletionRule ?? 'AnySession')
            : 'AnySession';
        $minimumSessions = max((int) ($program->CertificationMinimumSessions ?? 1), 1);

        if ($rule === 'AllSessions') {
            $requiredSessions = max($totalSessions, 0);
            $label = 'Attend all sessions in this program';
        } elseif ($rule === 'MinSessions') {
            $requiredSessions = $minimumSessions;
            $label = 'Attend at least ' . $minimumSessions . ' session(s)';
        } else {
            $requiredSessions = 1;
            $label = 'Attend at least one session';
        }

        return [
            'scope' => $scope,
            'rule' => $rule,
            'required_sessions' => $requiredSessions,
            'label' => $label,
            'total_sessions' => max($totalSessions, 0),
        ];
    }

    private function getProgramAttendedSessionCounts(int $programId): Collection
    {
        return TrainingSessionParticipant::query()
            ->join('t_CRMTrainingSessions as sess', 'sess.Id', '=', 't_CRMTrainingSessionParticipants.SessionID')
            ->where('sess.ProgramID', $programId)
            ->where('t_CRMTrainingSessionParticipants.AttendanceStatus', 'Present')
            ->selectRaw('t_CRMTrainingSessionParticipants.ClientID as ClientID, COUNT(DISTINCT t_CRMTrainingSessionParticipants.SessionID) as AttendedSessions')
            ->groupBy('t_CRMTrainingSessionParticipants.ClientID')
            ->get()
            ->mapWithKeys(function ($row) {
                return [(string) $row->ClientID => (int) $row->AttendedSessions];
            });
    }

    private function isProgramParticipantEligibleForCertification(int $attendedSessions, array $config): bool
    {
        $rule = (string) ($config['rule'] ?? 'AnySession');
        $requiredSessions = (int) ($config['required_sessions'] ?? 1);
        $totalSessions = (int) ($config['total_sessions'] ?? 0);

        if ($rule === 'AllSessions') {
            return $totalSessions > 0 && $attendedSessions >= $requiredSessions;
        }

        return $attendedSessions >= max($requiredSessions, 1);
    }

    private function generateProgramCertificateDocument(
        TrainingProgram $program,
        Client $client,
        ?TrainingCertificateTemplate $selectedTemplate,
        string $certificationName,
        ?string $issuingBody,
        ?string $certificateNumber,
        string $issuedOn,
        ?string $expiresOn,
        ?string $preparedTemplateBody = null,
        ?string $preparedBackgroundImageDataUri = null
    ): int {
        $participantName = trim((string) ($client->Name ?? $client->ClientID));
        $programName = trim((string) ($program->Title ?? $certificationName));
        $issuedOnDate = Carbon::parse($issuedOn);
        $expiresOnDate = $expiresOn ? Carbon::parse($expiresOn) : null;

        $tokenValues = [
            '{{participant_name}}' => $participantName,
            '{{client_id}}' => (string) $client->ClientID,
            '{{program_name}}' => $programName,
            '{{session_title}}' => 'Program Completion',
            '{{issue_date}}' => $issuedOnDate->format('jS \d\a\y \o\f F, Y'),
            '{{expiry_date}}' => $expiresOnDate?->format('jS \d\a\y \o\f F, Y') ?? '',
            '{{certificate_number}}' => $certificateNumber ?? '',
            '{{issuing_body}}' => $issuingBody ?? '',
        ];

        $templateBody = $preparedTemplateBody ?? $this->resolveCertificateTemplateBody($selectedTemplate);
        $renderedTemplateBody = strtr($templateBody, $tokenValues);
        $backgroundImageDataUri = $preparedBackgroundImageDataUri ?? $this->resolveCertificateBackgroundImageDataUri($selectedTemplate);

        $pdf = Pdf::loadView('crm.training.certificates.print', [
            'participantName' => $participantName,
            'programName' => $programName,
            'sessionTitle' => 'Program Completion',
            'certificationName' => $certificationName,
            'certificateNumber' => $certificateNumber,
            'issuingBody' => $issuingBody ?: config('app.name'),
            'issuedOnLabel' => $issuedOnDate->format('jS \d\a\y \o\f F, Y'),
            'expiresOnLabel' => $expiresOnDate?->format('jS \d\a\y \o\f F, Y'),
            'sessionDateLabel' => null,
            'renderedTemplateBody' => $renderedTemplateBody,
            'backgroundImageDataUri' => $backgroundImageDataUri,
        ])->setPaper('a4', 'landscape');
        $pdfOutput = $pdf->output();
        unset($pdf);

        $document = DocumentService::createContent(
            RepositoryService::module(ModulesEnum::CRM),
            ExtensionsEnum::Pdf,
            $this->buildProgramCertificateFileName($participantName, $programName, $issuedOnDate),
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

    private function resolveCertificateBackgroundImageDataUri(?TrainingCertificateTemplate $selectedTemplate): ?string
    {
        if (!$selectedTemplate) {
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
        if (!preg_match('/^assets\/certificates\/templates\/[A-Za-z0-9._-]+$/', $normalizedPath)) {
            return null;
        }

        $fullPath = public_path($normalizedPath);
        if (!is_file($fullPath)) {
            return null;
        }

        $content = @file_get_contents($fullPath);
        if ($content === false || $content === '') {
            return null;
        }

        $mimeType = @mime_content_type($fullPath) ?: 'image/svg+xml';

        return 'data:' . $mimeType . ';base64,' . base64_encode($content);
    }

    private function buildProgramCertificateNumber(
        ?string $prefix,
        TrainingProgram $program,
        string $clientId,
        string $issuedOn,
        int $sequence
    ): string {
        $normalizedPrefix = trim((string) $prefix);
        if ($normalizedPrefix === '') {
            $normalizedPrefix = 'CERT-PROG-' . $program->Id . '-' . Carbon::parse($issuedOn)->format('Ymd');
        }

        return strtoupper($normalizedPrefix) . '-' . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT) . '-' . $clientId;
    }

    private function sendProgramCertificateEmail(
        TrainingProgram $program,
        Client $client,
        TrainingCertificate $certificate
    ): bool {
        try {
            $email = trim((string) ($client->Email ?? ''));
            if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
            }

            $document = $certificate->document ?: ($certificate->DocumentId ? Document::find($certificate->DocumentId) : null);
            if (!$document) {
                return false;
            }

            $fileContent = (new DocumentService($document))->getFileContent(false);
            if ($fileContent === '') {
                return false;
            }

            $participantName = trim((string) ($client->Name ?? $client->ClientID));
            $subject = 'Training Certificate - ' . ($certificate->CertificationName ?: $program->Title);
            $body = '<p>Dear ' . e($participantName) . ',</p>'
                . '<p>Your program completion certificate for <strong>' . e((string) $program->Title) . '</strong> is attached.</p>'
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
                $document->Name ?: ('program-certificate-' . $client->ClientID . '.pdf'),
                auth()->user()
            );

            $service->send();

            return true;
        } catch (\Throwable $e) {
            report($e);
            return false;
        }
    }

    private function buildProgramCertificateFileName(string $participantName, string $programName, Carbon $issuedOn): string
    {
        $programSlug = Str::slug($programName);
        $participantSlug = Str::slug($participantName);

        if ($programSlug === '') {
            $programSlug = 'program';
        }
        if ($participantSlug === '') {
            $participantSlug = 'participant';
        }

        $base = 'training-program-certificate-' . $programSlug . '-' . $participantSlug . '-' . $issuedOn->format('Ymd');
        return substr($base, 0, 170) . '.pdf';
    }

    private function getTargetClientIds(TrainingProgram $program): Collection
    {
        return $program->targets()
            ->where('TargetType', 'Client')
            ->pluck('TargetID')
            ->map(fn ($value) => (string) $value)
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

    private function resolveValidClientIds(Collection $candidateClientIds): Collection
    {
        $normalized = $candidateClientIds
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->values();

        if ($normalized->isEmpty()) {
            return collect();
        }

        return Client::query()
            ->whereIn('ClientID', $normalized->all())
            ->pluck('ClientID')
            ->map(fn ($value) => (string) $value)
            ->filter()
            ->unique()
            ->values();
    }

    private function appendParticipants(TrainingProgram $program, Collection $validClientIds): int
    {
        if ($validClientIds->isEmpty()) {
            return 0;
        }

        $existingClientIds = TrainingProgramTarget::query()
            ->where('ProgramID', $program->Id)
            ->where('TargetType', 'Client')
            ->whereIn('TargetID', $validClientIds->all())
            ->pluck('TargetID')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values();

        $newClientIds = $validClientIds->diff($existingClientIds)->values();
        if ($newClientIds->isEmpty()) {
            return 0;
        }

        $now = now();
        $insert = $newClientIds->map(function (string $clientId) use ($program, $now) {
            return [
                'ProgramID' => $program->Id,
                'TargetType' => 'Client',
                'TargetID' => $clientId,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => $now,
            ];
        })->all();

        TrainingProgramTarget::insert($insert);

        return count($insert);
    }

    private function getAccessibleClientMarketingLists(): Collection
    {
        $actorId = auth()->id();

        $lists = MarketingList::query()
            ->where(function ($query) use ($actorId) {
                $query->where('Visibility', VisibilityEnum::Public->value)
                    ->orWhere(function ($privateQuery) use ($actorId) {
                        $privateQuery->where('Visibility', VisibilityEnum::Private->value)
                            ->where('CreatedBy', $actorId);
                    });
            })
            ->where(function ($query) {
                $query->where('Source', Client::getPrimaryKey())
                    ->orWhereNull('Source');
            })
            ->orderBy('Label')
            ->get(['MarketingListID', 'Label', 'Type', 'Source']);

        return $lists->map(function (MarketingList $list) {
            return (object) [
                'MarketingListID' => $list->MarketingListID,
                'Label' => $list->Label,
                'Type' => $list->Type?->name ?? 'Unknown',
                'SourceLabel' => (new ListService($list))->source(),
            ];
        });
    }

    private function findAccessibleClientMarketingList(int $marketingListId): ?MarketingList
    {
        $actorId = auth()->id();

        return MarketingList::query()
            ->where('MarketingListID', $marketingListId)
            ->where(function ($query) use ($actorId) {
                $query->where('Visibility', VisibilityEnum::Public->value)
                    ->orWhere(function ($privateQuery) use ($actorId) {
                        $privateQuery->where('Visibility', VisibilityEnum::Private->value)
                            ->where('CreatedBy', $actorId);
                    });
            })
            ->where(function ($query) {
                $query->where('Source', Client::getPrimaryKey())
                    ->orWhereNull('Source');
            })
            ->first();
    }

    private function resolveClientIdsFromMarketingList(MarketingList $marketingList): Collection
    {
        if ($marketingList->Type?->value === MarketingListEnum::Static->value) {
            return $marketingList->parties()
                ->where('Party', Client::getPrimaryKey())
                ->pluck('PartyID')
                ->map(fn ($value) => (string) $value)
                ->filter()
                ->unique()
                ->values();
        }

        if ($marketingList->Type?->value === MarketingListEnum::Dynamic->value) {
            if ($marketingList->Source !== Client::getPrimaryKey()) {
                return collect();
            }

            try {
                return (new DynamicListService($marketingList))
                    ->query()
                    ->pluck('ClientID')
                    ->map(fn ($value) => (string) $value)
                    ->filter()
                    ->unique()
                    ->values();
            } catch (ErroredException) {
                return collect();
            }
        }

        return collect();
    }
}
