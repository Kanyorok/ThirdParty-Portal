<?php

namespace App\Http\Controllers\CRM\Training;

use App\Http\Controllers\Controller;
use App\Models\BR\Client;
use App\Models\CRM\Training\TrainingCertificate;
use App\Models\CRM\Training\TrainingProgram;
use App\Models\CRM\Training\TrainingSession;
use App\Models\CRM\Training\TrainingSessionFeedback;
use App\Models\CRM\Training\TrainingSessionParticipant;
use Illuminate\Support\Facades\DB;

class TrainingReportController extends Controller
{
    public function index()
    {
        $totalPrograms = TrainingProgram::count();
        $totalSessions = TrainingSession::count();
        $totalParticipants = TrainingSessionParticipant::count();
        $completedSessions = TrainingSession::where('Status', 'Completed')->count();

        $attendanceBySession = TrainingSession::with('program')
            ->withCount([
                'participants as total_participants',
                'participants as present_count' => fn ($q) => $q->where('AttendanceStatus', 'Present'),
                'participants as late_count' => fn ($q) => $q->where('AttendanceStatus', 'Late'),
                'participants as absent_count' => fn ($q) => $q->where('AttendanceStatus', 'Absent'),
            ])
            ->orderByDesc('StartDate')
            ->limit(10)
            ->get();

        $hoursByClientRaw = DB::table('t_CRMTrainingSessionParticipants as tsp')
            ->join('t_CRMTrainingSessions as ts', 'tsp.SessionID', '=', 'ts.Id')
            ->join('t_CRMTrainingPrograms as tp', 'ts.ProgramID', '=', 'tp.Id')
            ->whereIn('tsp.AttendanceStatus', ['Present', 'Late'])
            ->select(
                'tsp.ClientID',
                DB::raw('SUM(ISNULL(tp.DurationHours, 0)) as Hours')
            )
            ->groupBy('tsp.ClientID')
            ->orderByDesc('Hours')
            ->limit(50)
            ->get();

        $clientIds = $hoursByClientRaw->pluck('ClientID')->map(fn ($id) => (string)$id)->all();
        $clients = collect();
        if (!empty($clientIds)) {
            $clients = Client::query()
                ->whereIn('ClientID', $clientIds)
                ->get(['ClientID', 'Name', 'ClientTypeID'])
                ->keyBy('ClientID');
        }

        $hoursByEmployee = $hoursByClientRaw
            ->take(10)
            ->map(function ($row) use ($clients) {
                $client = $clients->get((string)$row->ClientID);

                return (object) [
                    'EmployeeName' => $client?->Name ?? (string)$row->ClientID,
                    'Hours' => $row->Hours,
                ];
            });

        $typeSummary = [];
        foreach ($hoursByClientRaw as $row) {
            $client = $clients->get((string)$row->ClientID);
            $type = $client?->ClientTypeID ?: 'UNSPECIFIED';

            if (!isset($typeSummary[$type])) {
                $typeSummary[$type] = [
                    'Department' => $type,
                    'EmployeeCount' => 0,
                    'Hours' => 0,
                    'seen' => [],
                ];
            }

            if (!isset($typeSummary[$type]['seen'][(string)$row->ClientID])) {
                $typeSummary[$type]['EmployeeCount']++;
                $typeSummary[$type]['seen'][(string)$row->ClientID] = true;
            }
            $typeSummary[$type]['Hours'] += (float)$row->Hours;
        }

        $hoursByDepartment = collect($typeSummary)
            ->map(function (array $row) {
                unset($row['seen']);

                return (object) $row;
            })
            ->sortByDesc('Hours')
            ->values();

        $mandatoryPrograms = TrainingProgram::with('targets')
            ->where('IsMandatory', 1)
            ->orderBy('Title')
            ->get();

        $mandatoryCompliance = $mandatoryPrograms->map(function (TrainingProgram $program) {
            $targetClientIds = $this->resolveTargetClients($program);
            $totalTargets = count($targetClientIds);

            $completedCount = $totalTargets
                ? TrainingSessionParticipant::whereIn('ClientID', $targetClientIds)
                    ->whereHas('session', fn ($q) => $q->where('ProgramID', $program->Id))
                    ->where(function ($q) {
                        $q->whereIn('Status', ['Completed', 'Attended'])
                          ->orWhereIn('AttendanceStatus', ['Present', 'Late']);
                    })
                    ->select('ClientID')
                    ->distinct()
                    ->count('ClientID')
                : 0;

            return [
                'program' => $program,
                'total' => $totalTargets,
                'completed' => $completedCount,
                'percent' => $totalTargets ? round(($completedCount / $totalTargets) * 100, 2) : 0,
            ];
        });

        $expiringCertificates = TrainingCertificate::with(['client', 'session.program'])
            ->whereNotNull('ExpiresOn')
            ->whereDate('ExpiresOn', '>=', now()->toDateString())
            ->whereDate('ExpiresOn', '<=', now()->addDays(90)->toDateString())
            ->orderBy('ExpiresOn')
            ->limit(15)
            ->get();

        $programCosts = TrainingProgram::orderBy('Title')
            ->get(['Title', 'BudgetedCost', 'ActualCost']);

        $feedbackSummary = TrainingSessionFeedback::select(
                'SessionID',
                DB::raw('AVG(CAST(RatingContent as float)) as AvgContent'),
                DB::raw('AVG(CAST(RatingTrainer as float)) as AvgTrainer'),
                DB::raw('AVG(CAST(RatingRelevance as float)) as AvgRelevance')
            )
            ->groupBy('SessionID')
            ->get()
            ->keyBy('SessionID');

        return view('crm.training.reports.index', compact(
            'totalPrograms',
            'totalSessions',
            'totalParticipants',
            'completedSessions',
            'attendanceBySession',
            'hoursByDepartment',
            'hoursByEmployee',
            'mandatoryCompliance',
            'expiringCertificates',
            'programCosts',
            'feedbackSummary'
        ));
    }

    private function resolveTargetClients(TrainingProgram $program): array
    {
        $targets = $program->targets;
        if ($targets->isEmpty()) {
            return [];
        }

        return $targets
            ->where('TargetType', 'Client')
            ->pluck('TargetID')
            ->map(fn ($id) => (string)$id)
            ->unique()
            ->values()
            ->all();
    }
}
