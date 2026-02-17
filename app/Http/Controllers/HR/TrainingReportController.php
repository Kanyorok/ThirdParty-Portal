<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\TrainingCertificate;
use App\Models\HR\TrainingProgram;
use App\Models\HR\TrainingSession;
use App\Models\HR\TrainingSessionFeedback;
use App\Models\HR\TrainingSessionParticipant;
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

        $hoursByDepartment = DB::table('t_HRTrainingSessionParticipants as tsp')
            ->join('t_HREmployees as e', 'tsp.EmployeeID', '=', 'e.Id')
            ->join('t_Departments as d', 'e.DepartmentID', '=', 'd.Id')
            ->join('t_HRTrainingSessions as ts', 'tsp.SessionID', '=', 'ts.Id')
            ->join('t_HRTrainingPrograms as tp', 'ts.ProgramID', '=', 'tp.Id')
            ->whereIn('tsp.AttendanceStatus', ['Present', 'Late'])
            ->select(
                'd.Name as Department',
                DB::raw('SUM(ISNULL(tp.DurationHours, 0)) as Hours'),
                DB::raw('COUNT(DISTINCT tsp.EmployeeID) as EmployeeCount')
            )
            ->groupBy('d.Name')
            ->orderByDesc('Hours')
            ->limit(10)
            ->get();

        $hoursByEmployee = DB::table('t_HRTrainingSessionParticipants as tsp')
            ->join('t_HREmployees as e', 'tsp.EmployeeID', '=', 'e.Id')
            ->join('t_HRTrainingSessions as ts', 'tsp.SessionID', '=', 'ts.Id')
            ->join('t_HRTrainingPrograms as tp', 'ts.ProgramID', '=', 'tp.Id')
            ->whereIn('tsp.AttendanceStatus', ['Present', 'Late'])
            ->select(
                DB::raw("CONCAT(e.FirstName, ' ', e.LastName) as EmployeeName"),
                DB::raw('SUM(ISNULL(tp.DurationHours, 0)) as Hours')
            )
            ->groupBy('e.FirstName', 'e.LastName')
            ->orderByDesc('Hours')
            ->limit(10)
            ->get();

        $mandatoryPrograms = TrainingProgram::with('targets')
            ->where('IsMandatory', 1)
            ->orderBy('Title')
            ->get();

        $mandatoryCompliance = $mandatoryPrograms->map(function (TrainingProgram $program) {
            $targetEmployeeIds = $this->resolveTargetEmployees($program);
            $totalTargets = count($targetEmployeeIds);

            $completedCount = $totalTargets
                ? TrainingSessionParticipant::whereIn('EmployeeID', $targetEmployeeIds)
                    ->whereHas('session', fn ($q) => $q->where('ProgramID', $program->Id))
                    ->where(function ($q) {
                        $q->whereIn('Status', ['Completed', 'Attended'])
                          ->orWhereIn('AttendanceStatus', ['Present', 'Late']);
                    })
                    ->select('EmployeeID')
                    ->distinct()
                    ->count('EmployeeID')
                : 0;

            return [
                'program' => $program,
                'total' => $totalTargets,
                'completed' => $completedCount,
                'percent' => $totalTargets ? round(($completedCount / $totalTargets) * 100, 2) : 0,
            ];
        });

        $expiringCertificates = TrainingCertificate::with(['employee', 'session.program'])
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

        return view('hr.training.reports.index', compact(
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

    private function resolveTargetEmployees(TrainingProgram $program): array
    {
        $targets = $program->targets;
        if ($targets->isEmpty()) {
            return Employee::where('IsActive', 1)->pluck('Id')->toArray();
        }

        $employeeIds = collect();
        $deptIds = $targets->where('TargetType', 'Department')->pluck('TargetID')->all();
        if ($deptIds) {
            $employeeIds = $employeeIds->merge(Employee::whereIn('DepartmentID', $deptIds)->pluck('Id'));
        }

        $gradeIds = $targets->where('TargetType', 'Grade')->pluck('TargetID')->all();
        if ($gradeIds) {
            $employeeIds = $employeeIds->merge(Employee::whereIn('GradeID', $gradeIds)->pluck('Id'));
        }

        $roleIds = $targets->where('TargetType', 'Role')->pluck('TargetID')->all();
        if ($roleIds) {
            $employeeIds = $employeeIds->merge(Employee::whereIn('RoleID', $roleIds)->pluck('Id'));
        }

        return $employeeIds->unique()->values()->all();
    }
}
