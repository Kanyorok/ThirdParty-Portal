<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\InterviewSession;
use App\Models\HR\InterviewSessionCandidate;
use App\Models\HR\InterviewSessionNotification;
use App\Models\HR\InterviewSessionPanel;
use App\Models\HR\InterviewSessionQuestion;
use App\Models\HR\InterviewSessionQuestionAssignment;
use App\Models\HR\InterviewSessionScore;
use App\Models\HR\JobApplication;
use App\Models\HR\JobInterviewQuestion;
use App\Models\HR\JobOpening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobInterviewController extends Controller
{
    private const SESSION_STATUSES = ['Scheduled', 'In Progress', 'Completed', 'Cancelled'];
    private const CANDIDATE_STATUSES = ['Scheduled', 'Completed', 'Passed', 'Failed', 'No-show', 'Proceed', 'Offer'];

    public function index()
    {
        $sessions = InterviewSession::with(['opening'])
            ->withCount('candidates')
            ->orderByDesc('InterviewDate')
            ->paginate(20);

        return view('hr.recruitment.interviews.index', compact('sessions'));
    }

    public function create(Request $request)
    {
        $openings = JobOpening::whereIn('Status', ['Open', 'Draft'])->orderBy('Title')->get(['Id', 'Title']);
        $selectedOpening = null;
        $sourceSessions = collect();
        $selectedSourceSession = null;
        $candidates = collect();

        if ($request->filled('opening_id')) {
            $selectedOpening = $openings->firstWhere('Id', (int)$request->opening_id);
            if ($selectedOpening) {
                $sourceSessions = InterviewSession::where('JobOpeningID', $selectedOpening->Id)
                    ->orderBy('RoundNo')
                    ->get();
            }
        }

        if ($request->filled('source_session_id')) {
            $selectedSourceSession = $sourceSessions->firstWhere('Id', (int)$request->source_session_id);
        }

        if ($selectedOpening) {
            if ($selectedSourceSession) {
                $sessionCandidates = InterviewSessionCandidate::with(['application.applicant'])
                    ->where('SessionID', $selectedSourceSession->Id)
                    ->whereIn('Status', ['Passed', 'Proceed'])
                    ->get();
                $candidates = $sessionCandidates->map(function ($candidate) {
                    $name = trim(($candidate->application?->applicant?->FirstName ?? '') . ' ' . ($candidate->application?->applicant?->LastName ?? ''));
                    return (object) [
                        'ApplicationID' => $candidate->ApplicationID,
                        'Name' => $name,
                        'Status' => $candidate->Status,
                    ];
                });
            } else {
                $applications = JobApplication::with('applicant')
                    ->where('JobOpeningID', $selectedOpening->Id)
                    ->where('Status', 'Shortlisted')
                    ->orderBy('Id')
                    ->get();
                $candidates = $applications->map(function ($application) {
                    $name = trim(($application->applicant?->FirstName ?? '') . ' ' . ($application->applicant?->LastName ?? ''));
                    return (object) [
                        'ApplicationID' => $application->Id,
                        'Name' => $name,
                        'Status' => $application->Status,
                    ];
                });
            }
        }

        $statusList = self::SESSION_STATUSES;

        return view('hr.recruitment.interviews.create', compact(
            'openings',
            'selectedOpening',
            'sourceSessions',
            'selectedSourceSession',
            'candidates',
            'statusList'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'JobOpeningID' => ['required', 'integer', 'exists:t_HRJobOpenings,Id'],
            'RoundNo' => ['required', 'integer', 'min:1'],
            'RoundLabel' => ['nullable', 'string', 'max:50'],
            'InterviewType' => ['nullable', 'string', 'max:100'],
            'InterviewDate' => ['required', 'date'],
            'Location' => ['nullable', 'string', 'max:150'],
            'Status' => ['nullable', 'string', 'max:30'],
            'CandidateIDs' => ['required', 'array'],
            'CandidateIDs.*' => ['integer', 'exists:t_HRJobApplications,Id'],
            'SlotTimes' => ['nullable', 'array'],
            'SlotTimes.*' => ['nullable', 'date_format:H:i'],
        ]);

        $slotTimes = $data['SlotTimes'] ?? [];
        foreach ($data['CandidateIDs'] as $candidateId) {
            if (empty($slotTimes[$candidateId])) {
                return redirect()->back()->withErrors([
                    'SlotTimes' => 'Provide a time for every selected candidate.',
                ]);
            }
        }

        $session = InterviewSession::create([
            'JobOpeningID' => $data['JobOpeningID'],
            'RoundNo' => $data['RoundNo'],
            'RoundLabel' => $data['RoundLabel'] ?? null,
            'InterviewType' => $data['InterviewType'] ?? null,
            'InterviewDate' => \Carbon\Carbon::parse($data['InterviewDate'])->format('Y-m-d'),
            'Location' => $data['Location'] ?? null,
            'Status' => $data['Status'] ?? 'Scheduled',
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        $applications = JobApplication::whereIn('Id', $data['CandidateIDs'])->get()->keyBy('Id');
        foreach ($data['CandidateIDs'] as $candidateId) {
            $application = $applications->get((int)$candidateId);
            if (!$application) {
                continue;
            }
            $slot = \Carbon\Carbon::parse($data['InterviewDate'].' '.$slotTimes[$candidateId])->format('Y-m-d H:i:s');

            InterviewSessionCandidate::create([
                'SessionID' => $session->Id,
                'ApplicationID' => $application->Id,
                'SlotTime' => $slot,
                'Status' => 'Scheduled',
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);

            if (!in_array($application->Status, ['Rejected', 'Hired'], true)) {
                $application->update([
                    'Status' => 'Interview',
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]);
            }
        }

        return redirect()->route('hr.recruitment.interviews.index')->with('success', 'Interview session scheduled.');
    }

    public function show($id)
    {
        $session = InterviewSession::with([
            'opening',
            'candidates.application.applicant',
            'candidates.application.documents',
            'panelMembers.employee.department',
            'panelMembers.employee.role',
            'sessionQuestions.question.group',
            'questionAssignments',
            'notifications.employee',
        ])->findOrFail($id);

        $libraryQuestions = JobInterviewQuestion::with('group')
            ->where('IsActive', 1)
            ->orderBy('GroupID')
            ->orderBy('Id')
            ->get();
        $questionGroups = $libraryQuestions->groupBy(fn ($q) => $q->group?->Name ?? 'General');
        $openingQuestionIds = $session->opening
            ? $session->opening->questions()->pluck('t_HRJobInterviewQuestions.Id')->all()
            : [];
        $openingQuestionIds = array_map('intval', $openingQuestionIds);
        $selectedQuestionIds = $session->sessionQuestions->pluck('QuestionID')->all();
        $selectedQuestionIds = array_map('intval', $selectedQuestionIds);
        $assignments = $session->questionAssignments->keyBy('QuestionID');

        $employees = Employee::with(['department', 'role'])
            ->whereNull('DeletedOn')
            ->orderBy('FirstName')
            ->get(['Id', 'FirstName', 'LastName', 'DepartmentID', 'RoleID']);

        $panelists = $session->panelMembers->pluck('EmployeeID')->all();

        $candidateIds = $session->candidates->pluck('Id')->all();
        $scoreSummary = InterviewSessionScore::select('SessionCandidateID', DB::raw('AVG(Score) as AvgScore'))
            ->whereIn('SessionCandidateID', $candidateIds)
            ->groupBy('SessionCandidateID')
            ->get()
            ->keyBy('SessionCandidateID');

        return view('hr.recruitment.interviews.show', compact(
            'session',
            'questionGroups',
            'openingQuestionIds',
            'selectedQuestionIds',
            'assignments',
            'employees',
            'panelists',
            'scoreSummary'
        ));
    }

    public function edit($id)
    {
        $session = InterviewSession::with('opening')->findOrFail($id);
        $statusList = self::SESSION_STATUSES;

        return view('hr.recruitment.interviews.edit', compact('session', 'statusList'));
    }

    public function update(Request $request, $id)
    {
        $session = InterviewSession::findOrFail($id);
        $data = $request->validate([
            'RoundNo' => ['required', 'integer', 'min:1'],
            'RoundLabel' => ['nullable', 'string', 'max:50'],
            'InterviewType' => ['nullable', 'string', 'max:100'],
            'InterviewDate' => ['required', 'date'],
            'Location' => ['nullable', 'string', 'max:150'],
            'Status' => ['nullable', 'string', 'max:30'],
            'Notes' => ['nullable', 'string'],
        ]);

        $session->update([
            'RoundNo' => $data['RoundNo'],
            'RoundLabel' => $data['RoundLabel'] ?? null,
            'InterviewType' => $data['InterviewType'] ?? null,
            'InterviewDate' => \Carbon\Carbon::parse($data['InterviewDate'])->format('Y-m-d'),
            'Location' => $data['Location'] ?? null,
            'Status' => $data['Status'] ?? $session->Status,
            'Notes' => $data['Notes'] ?? null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.interviews.show', $session->Id)->with('success', 'Session updated.');
    }

    public function addPanel(Request $request, $id)
    {
        $session = InterviewSession::with('opening')->findOrFail($id);
        $data = $request->validate([
            'PanelistIDs' => ['required', 'array'],
            'PanelistIDs.*' => ['integer', 'exists:t_HREmployees,Id'],
            'Role' => ['nullable', 'string', 'max:100'],
        ]);

        $existing = InterviewSessionPanel::where('SessionID', $session->Id)
            ->pluck('EmployeeID')
            ->all();

        foreach ($data['PanelistIDs'] as $employeeId) {
            if (in_array($employeeId, $existing, true)) {
                continue;
            }
            InterviewSessionPanel::create([
                'SessionID' => $session->Id,
                'EmployeeID' => $employeeId,
                'Role' => $data['Role'] ?? null,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);

            $message = "You have been added to the panel for {$session->opening?->Title} (Round {$session->RoundNo}).";
            InterviewSessionNotification::create([
                'SessionID' => $session->Id,
                'EmployeeID' => $employeeId,
                'Message' => $message,
                'CreatedOn' => now(),
            ]);
        }

        return redirect()->route('hr.recruitment.interviews.show', $session->Id)
            ->with('success', 'Panel members saved.');
    }

    public function removePanel($id, $panelId)
    {
        $panel = InterviewSessionPanel::where('SessionID', $id)->findOrFail($panelId);
        $panel->delete();

        return redirect()->route('hr.recruitment.interviews.show', $id)
            ->with('success', 'Panel member removed.');
    }

    public function updateQuestions(Request $request, $id)
    {
        $session = InterviewSession::findOrFail($id);
        $data = $request->validate([
            'QuestionIDs' => ['nullable', 'array'],
            'QuestionIDs.*' => ['integer', 'exists:t_HRJobInterviewQuestions,Id'],
        ]);

        InterviewSessionQuestion::where('SessionID', $session->Id)->delete();
        $questionIds = $data['QuestionIDs'] ?? [];
        foreach ($questionIds as $questionId) {
            InterviewSessionQuestion::create([
                'SessionID' => $session->Id,
                'QuestionID' => $questionId,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        if (empty($questionIds)) {
            InterviewSessionQuestionAssignment::where('SessionID', $session->Id)->delete();
        } else {
            InterviewSessionQuestionAssignment::where('SessionID', $session->Id)
                ->whereNotIn('QuestionID', $questionIds)
                ->delete();
        }

        return redirect()->route('hr.recruitment.interviews.show', $session->Id)
            ->with('success', 'Questions updated for this round.');
    }

    public function assignQuestions(Request $request, $id)
    {
        $session = InterviewSession::findOrFail($id);
        $data = $request->validate([
            'Assignments' => ['required', 'array'],
            'Assignments.*' => ['nullable', 'integer', 'exists:t_HREmployees,Id'],
        ]);

        $sessionQuestionIds = InterviewSessionQuestion::where('SessionID', $session->Id)
            ->pluck('QuestionID')
            ->all();

        foreach ($data['Assignments'] as $questionId => $panelistId) {
            if (!in_array((int)$questionId, $sessionQuestionIds, true)) {
                continue;
            }

            $existing = InterviewSessionQuestionAssignment::where('SessionID', $session->Id)
                ->where('QuestionID', $questionId)
                ->first();
            if (!$panelistId) {
                if ($existing) {
                    $existing->delete();
                }
                continue;
            }

            if ($existing) {
                $existing->update([
                    'PanelistID' => $panelistId,
                    'AssignedBy' => auth()->id(),
                    'AssignedOn' => now(),
                ]);
            } else {
                InterviewSessionQuestionAssignment::create([
                    'SessionID' => $session->Id,
                    'QuestionID' => $questionId,
                    'PanelistID' => $panelistId,
                    'AssignedBy' => auth()->id(),
                    'AssignedOn' => now(),
                ]);
            }
        }

        return redirect()->route('hr.recruitment.interviews.show', $session->Id)
            ->with('success', 'Question assignments saved.');
    }

    public function evaluate(Request $request, $sessionId, $candidateId)
    {
        $candidate = InterviewSessionCandidate::with([
            'application.applicant',
            'application.documents',
            'application.opening',
            'session.panelMembers.employee',
            'session.sessionQuestions.question.group',
            'session.questionAssignments',
        ])->findOrFail($candidateId);

        if ((int)$candidate->SessionID !== (int)$sessionId) {
            abort(404);
        }

        $panelistId = $request->query('panelist_id');
        $assignments = $candidate->session->questionAssignments->keyBy('QuestionID');
        $questions = $candidate->session->sessionQuestions->map(fn ($item) => $item->question)->filter();

        if ($panelistId && $assignments->isNotEmpty()) {
            $questions = $questions->filter(function ($question) use ($assignments, $panelistId) {
                $assigned = $assignments[$question->Id]->PanelistID ?? null;
                return (int)$assigned === (int)$panelistId;
            })->values();
        }

        $questionGroups = $questions->groupBy(fn ($q) => $q->group?->Name ?? 'General');

        $scores = InterviewSessionScore::where('SessionCandidateID', $candidate->Id)
            ->when($panelistId, fn ($q) => $q->where('PanelistID', $panelistId))
            ->get()
            ->keyBy('QuestionID');

        $panelistLocked = false;
        $panelistAverage = null;
        if ($panelistId) {
            $panelistLocked = InterviewSessionScore::where('SessionCandidateID', $candidate->Id)
                ->where('PanelistID', $panelistId)
                ->exists();
            $panelistAverage = InterviewSessionScore::where('SessionCandidateID', $candidate->Id)
                ->where('PanelistID', $panelistId)
                ->avg('Score');
        }

        $overallAverage = InterviewSessionScore::where('SessionCandidateID', $candidate->Id)
            ->avg('Score');

        return view('hr.recruitment.interviews.evaluate', compact(
            'candidate',
            'panelistId',
            'questionGroups',
            'scores',
            'panelistLocked',
            'panelistAverage',
            'overallAverage'
        ));
    }

    public function submitCandidateScores(Request $request, $sessionId, $candidateId)
    {
        $candidate = InterviewSessionCandidate::with('session')->findOrFail($candidateId);
        if ((int)$candidate->SessionID !== (int)$sessionId) {
            abort(404);
        }

        $data = $request->validate([
            'PanelistID' => ['required', 'integer', 'exists:t_HREmployees,Id'],
            'Status' => ['required', 'string', 'max:30'],
            'Scores' => ['required', 'array'],
            'Scores.*' => ['nullable', 'numeric'],
            'Comments' => ['nullable', 'array'],
            'Comments.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $alreadySubmitted = InterviewSessionScore::where('SessionCandidateID', $candidate->Id)
            ->where('PanelistID', $data['PanelistID'])
            ->exists();
        if ($alreadySubmitted) {
            return redirect()->route('hr.recruitment.interviews.candidates.evaluate', [
                $sessionId,
                $candidateId,
                'panelist_id' => $data['PanelistID'],
            ])->withErrors(['scores' => 'Scores for this panelist are already submitted and locked.']);
        }

        foreach ($data['Scores'] as $questionId => $score) {
            $comment = $data['Comments'][$questionId] ?? null;
            $existing = InterviewSessionScore::where('SessionCandidateID', $candidate->Id)
                ->where('QuestionID', $questionId)
                ->where('PanelistID', $data['PanelistID'])
                ->first();

            if ($existing) {
                continue;
            } else {
                InterviewSessionScore::create([
                    'SessionCandidateID' => $candidate->Id,
                    'QuestionID' => $questionId,
                    'PanelistID' => $data['PanelistID'],
                    'Score' => $score,
                    'Comment' => $comment,
                    'CreatedBy' => auth()->id(),
                    'CreatedOn' => now(),
                ]);
            }
        }

        $candidate->update([
            'Status' => $data['Status'],
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.recruitment.interviews.candidates.evaluate', [$sessionId, $candidateId, 'panelist_id' => $data['PanelistID']])
            ->with('success', 'Scores saved.');
    }

    public function bulkCandidateAction(Request $request, $sessionId)
    {
        $session = InterviewSession::with('candidates.application')->findOrFail($sessionId);
        $data = $request->validate([
            'CandidateIDs' => ['required', 'array'],
            'CandidateIDs.*' => ['integer'],
            'Action' => ['required', 'string', 'in:next_round,offer'],
        ]);

        $candidateIds = array_map('intval', $data['CandidateIDs']);
        $candidates = $session->candidates->whereIn('Id', $candidateIds);

        foreach ($candidates as $candidate) {
            if ($data['Action'] === 'offer') {
                $candidate->update([
                    'Status' => 'Offer',
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]);
                if ($candidate->application && !in_array($candidate->application->Status, ['Rejected', 'Withdrawn', 'Hired'], true)) {
                    $candidate->application->update([
                        'Status' => 'Offer',
                        'ModifiedBy' => auth()->id(),
                        'ModifiedOn' => now(),
                    ]);
                }
            } else {
                $candidate->update([
                    'Status' => 'Proceed',
                    'ModifiedBy' => auth()->id(),
                    'ModifiedOn' => now(),
                ]);
            }
        }

        return redirect()->route('hr.recruitment.interviews.show', $session->Id)
            ->with('success', 'Candidates updated.');
    }
}
