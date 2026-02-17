<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\KpiAppraisal;
use App\Models\HR\KpiAppraisalItem;
use App\Models\HR\KpiGoal;
use App\Models\HR\KpiPerspective;
use App\Models\HR\KpiPerspectiveWeight;
use App\Models\HR\KpiRatingScale;
use App\Models\HR\KpiScoreChart;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KpiAppraisalController extends Controller
{
    public function index(Request $request)
    {
        $query = KpiAppraisal::with(['employee','period','goal','items'])->orderByDesc('Id');
        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('year')) {
            $year = (int)$request->year;
            $query->whereHas('goal', function ($goalQuery) use ($year) {
                $goalQuery->where('PeriodYear', $year);
            });
        }
        if ($request->filled('employee_id')) {
            $query->where('EmployeeID', (int)$request->employee_id);
        }

        $appraisals = $query->paginate(30);
        $years = KpiGoal::whereIn('Id', KpiAppraisal::select('GoalID')->whereNotNull('GoalID'))
            ->select('PeriodYear')
            ->distinct()
            ->orderByDesc('PeriodYear')
            ->pluck('PeriodYear');
        $employees = \App\Models\HR\Employee::orderBy('FirstName')->get(['Id','FirstName','LastName','EmployeeNo']);
        $ratingScaleMap = KpiRatingScale::pluck('MaxScore', 'Id');

        return view('hr.kpi.appraisals.index', compact('appraisals', 'years', 'employees', 'ratingScaleMap'));
    }

    public function create(Request $request)
    {
        $goalId = $request->get('goal_id');
        $periodId = $request->get('period_id');
        $year = $request->get('year');
        $segment = $request->get('segment');
        $usedGoalIds = KpiAppraisal::pluck('GoalID')->filter()->values();
        $goals = KpiGoal::with(['employee','period'])
            ->where('Status', 'Approved')
            ->when($periodId, function ($q) use ($periodId) {
                $q->where('PeriodID', (int)$periodId);
            })
            ->when($year, function ($q) use ($year) {
                $q->where('PeriodYear', (int)$year);
            })
            ->when($segment, function ($q) use ($segment) {
                $q->where('PeriodSegment', (int)$segment);
            })
            ->when($usedGoalIds->isNotEmpty(), function ($q) use ($usedGoalIds) {
                $q->whereNotIn('Id', $usedGoalIds);
            })
            ->orderByDesc('Id')
            ->get();

        $goal = null;
        $items = collect();
        $perspectiveGroups = collect();
        $perspectives = collect();
        $weightMap = collect();
        if ($goalId) {
            $goal = $goals->firstWhere('Id', (int)$goalId);
            if ($goal) {
                $items = $goal->items()->with('kpiItem')->get();
                $perspectiveIds = $items->pluck('kpiItem.PerspectiveID')->filter()->unique()->values();
                $perspectives = KpiPerspective::whereIn('Id', $perspectiveIds)->get()->keyBy('Id');
                $weightMap = KpiPerspectiveWeight::resolveWeights(
                    $goal->PeriodID,
                    $goal->employee?->GradeID,
                    $goal->employee?->RoleID
                );
                $perspectiveGroups = $items->groupBy(function ($item) {
                    return $item->kpiItem?->PerspectiveID ?? 0;
                });
            }
        }
        $scales = KpiRatingScale::where('IsActive', 1)->orderBy('MinScore')->get(['Id','Name','MinScore','MaxScore']);
        $ratingScalePayload = $scales->map(function ($scale) {
            return [
                'Id' => $scale->Id,
                'Min' => (float)$scale->MinScore,
                'Max' => (float)$scale->MaxScore,
            ];
        })->values();
        $periods = \App\Models\HR\KpiPeriod::where('IsActive', 1)->orderBy('Name')->get(['Id','Name','Code','StartMonth','EndMonth']);
        $periodsPayload = $periods->map(function ($period) {
            $start = (int)($period->StartMonth ?? 1);
            $end = (int)($period->EndMonth ?? 12);
            $length = $end - $start + 1;
            $segmentCount = 1;
            if ($length > 0 && 12 % $length === 0) {
                $segmentCount = (int)(12 / $length);
            }

            return [
                'Id' => $period->Id,
                'Name' => $period->Name,
                'Code' => $period->Code,
                'StartMonth' => $period->StartMonth,
                'EndMonth' => $period->EndMonth,
                'SegmentCount' => $segmentCount,
            ];
        })->values();
        $yearOptions = [];
        $startYear = (int)now()->year;
        for ($i = 0; $i < 7; $i++) {
            $yearOptions[] = $startYear + $i;
        }

        return view('hr.kpi.appraisals.create', compact(
            'goals',
            'goal',
            'items',
            'scales',
            'perspectiveGroups',
            'perspectives',
            'weightMap',
            'periods',
            'periodsPayload',
            'yearOptions',
            'periodId',
            'year',
            'segment',
            'ratingScalePayload'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'GoalID' => ['required','exists:t_HRKPIGoals,Id'],
            'Comments' => ['nullable','string','max:500'],
            'OverallRatingID' => ['required','exists:t_HRKPIRatingScales,Id'],
            'Items' => ['required','array','min:1'],
            'Items.*.GoalItemID' => ['required','exists:t_HRKPIGoalItems,Id'],
            'Items.*.ActualValue' => ['nullable','numeric'],
            'Items.*.SelfRatingValue' => ['nullable','numeric','min:0'],
            'Items.*.SupervisorRatingValue' => ['nullable','numeric','min:0'],
            'Items.*.AppraiseeComments' => ['nullable','string','max:255'],
            'Items.*.AppraiserComments' => ['nullable','string','max:255'],
        ]);

        $goal = KpiGoal::with('employee')->findOrFail($data['GoalID']);
        if ($goal->Status !== 'Approved') {
            throw ValidationException::withMessages([
                'GoalID' => 'Only approved goals can be appraised.',
            ]);
        }
        $existing = KpiAppraisal::where('GoalID', $goal->Id)->first();
        if ($existing) {
            throw ValidationException::withMessages([
                'GoalID' => 'An appraisal already exists for this goal set.',
            ]);
        }

        $scale = KpiRatingScale::find($data['OverallRatingID']);
        $minScore = $scale ? (float)$scale->MinScore : 1.0;
        $maxScore = $scale ? (float)$scale->MaxScore : 5.0;
        $isSubmitting = $request->input('Action') === 'submit';
        $goalItems = $goal->items()->get()->keyBy('Id');
        $rawTotalScore = 0;

        $status = $isSubmitting ? 'Submitted' : 'Draft';
        $appraisal = KpiAppraisal::create([
            'GoalID' => $goal->Id,
            'EmployeeID' => $goal->EmployeeID,
            'PeriodID' => $goal->PeriodID,
            'Status' => $status,
            'TotalScore' => 0,
            'OverallRatingID' => $data['OverallRatingID'] ?? null,
            'AppraisedBy' => null,
            'AppraisedOn' => null,
            'Comments' => $data['Comments'] ?? null,
            'SubmittedBy' => $status === 'Submitted' ? auth()->id() : null,
            'SubmittedOn' => $status === 'Submitted' ? now() : null,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        foreach ($data['Items'] as $item) {
            $selfRating = $item['SelfRatingValue'] ?? null;
            $supervisorRating = $item['SupervisorRatingValue'] ?? null;
            if ($isSubmitting && $selfRating === null) {
                throw ValidationException::withMessages([
                    'Items' => 'Self rating is required for all items before submitting.',
                ]);
            }
            if ($selfRating !== null && ($selfRating < $minScore || $selfRating > $maxScore)) {
                throw ValidationException::withMessages([
                    'Items' => 'Self rating must be within the selected overall rating scale.',
                ]);
            }
            if ($supervisorRating !== null && ($supervisorRating < $minScore || $supervisorRating > $maxScore)) {
                throw ValidationException::withMessages([
                    'Items' => 'Supervisor rating must be within the selected overall rating scale.',
                ]);
            }
            $weight = (float)($goalItems[$item['GoalItemID']]->Weight ?? 0);
            $normalizedWeight = $weight > 1 ? $weight / 100 : $weight;
            $selfScore = $selfRating !== null ? $normalizedWeight * (float)$selfRating : null;
            $finalScore = $supervisorRating !== null ? $normalizedWeight * (float)$supervisorRating : null;
            $rawTotalScore += (float)($finalScore ?? $selfScore ?? 0);
            KpiAppraisalItem::create([
                'AppraisalID' => $appraisal->Id,
                'GoalItemID' => $item['GoalItemID'],
                'ActualValue' => $item['ActualValue'] ?? null,
                'SelfRatingScaleID' => null,
                'SelfRatingValue' => $selfRating,
                'SelfScore' => $selfScore,
                'SupervisorRatingScaleID' => null,
                'SupervisorRatingValue' => $supervisorRating,
                'FinalScore' => $finalScore,
                'Score' => $finalScore ?? $selfScore,
                'RatingScaleID' => null,
                'AppraiseeComments' => $item['AppraiseeComments'] ?? null,
                'AppraiserComments' => $item['AppraiserComments'] ?? null,
                'Comments' => $item['AppraiserComments'] ?? null,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        $totalScore = $maxScore > 0 ? ($rawTotalScore / $maxScore) * 100 : $rawTotalScore;
        $appraisal->update([
            'TotalScore' => $totalScore,
        ]);

        return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->with('success', 'KPI appraisal saved.');
    }

    public function show($id)
    {
        $appraisal = KpiAppraisal::with([
            'employee.branch',
            'employee.department',
            'employee.role',
            'employee.supervisor.branch',
            'employee.supervisor.department',
            'employee.supervisor.role',
            'period',
            'goal',
            'items.goalItem.kpiItem.perspective',
        ])->findOrFail($id);
        $gradeId = $appraisal->employee?->GradeID;
        $roleId = $appraisal->employee?->RoleID;
        $weightMap = KpiPerspectiveWeight::resolveWeights($appraisal->PeriodID, $gradeId, $roleId);
        $perspectiveIds = $appraisal->items->pluck('goalItem.kpiItem.PerspectiveID')->filter()->unique()->values();
        $perspectives = KpiPerspective::whereIn('Id', $perspectiveIds)->get()->keyBy('Id');
        $perspectiveGroups = $appraisal->items->groupBy(function ($item) {
            return $item->goalItem?->kpiItem?->PerspectiveID ?? 0;
        });
        $scales = KpiRatingScale::where('IsActive', 1)->orderBy('MinScore')->get(['Id','Name','MinScore','MaxScore']);
        $overallScale = $scales->firstWhere('Id', $appraisal->OverallRatingID);
        $overallMax = $overallScale?->MaxScore ? (float)$overallScale->MaxScore : null;
        $rawTotalScore = $appraisal->items->sum(fn ($item) => (float)($item->FinalScore ?? $item->Score));
        $totalScorePercent = $overallMax ? ($rawTotalScore / $overallMax) * 100 : (float)$appraisal->TotalScore;

        $chartRows = KpiScoreChart::where('IsActive', 1)
            ->where('RatingScaleID', $appraisal->OverallRatingID)
            ->orderBy('MinPercent')
            ->get();
        if ($chartRows->isEmpty()) {
            $chartRows = KpiScoreChart::where('IsActive', 1)
                ->whereNull('RatingScaleID')
                ->orderBy('MinPercent')
                ->get();
        }
        $finalRating = $chartRows->first(function ($row) use ($totalScorePercent) {
            $min = (float)$row->MinPercent;
            $max = $row->MaxPercent !== null ? (float)$row->MaxPercent : null;

            return $totalScorePercent >= $min && ($max === null || $totalScorePercent <= $max);
        });

        return view('hr.kpi.appraisals.show', compact(
            'appraisal',
            'scales',
            'perspectiveGroups',
            'perspectives',
            'weightMap',
            'totalScorePercent',
            'overallMax',
            'chartRows',
            'finalRating'
        ));
    }

    public function edit($id)
    {
        $appraisal = KpiAppraisal::with(['items.goalItem.kpiItem.perspective'])->findOrFail($id);
        if (! in_array($appraisal->Status, ['Draft','Returned','Rejected','Submitted'], true)) {
            return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->withErrors([
                'status' => 'Only draft/returned/rejected/submitted appraisals can be edited.',
            ]);
        }
        $gradeId = $appraisal->employee?->GradeID;
        $roleId = $appraisal->employee?->RoleID;
        $weightMap = KpiPerspectiveWeight::resolveWeights($appraisal->PeriodID, $gradeId, $roleId);
        $perspectiveIds = $appraisal->items->pluck('goalItem.kpiItem.PerspectiveID')->filter()->unique()->values();
        $perspectives = KpiPerspective::whereIn('Id', $perspectiveIds)->get()->keyBy('Id');
        $perspectiveGroups = $appraisal->items->groupBy(function ($item) {
            return $item->goalItem?->kpiItem?->PerspectiveID ?? 0;
        });
        $scales = KpiRatingScale::where('IsActive', 1)->orderBy('MinScore')->get(['Id','Name','MinScore','MaxScore']);
        $ratingScalePayload = $scales->map(function ($scale) {
            return [
                'Id' => $scale->Id,
                'Min' => (float)$scale->MinScore,
                'Max' => (float)$scale->MaxScore,
            ];
        })->values();

        return view('hr.kpi.appraisals.edit', compact('appraisal', 'scales', 'perspectiveGroups', 'perspectives', 'weightMap', 'ratingScalePayload'));
    }

    public function update(Request $request, $id)
    {
        $appraisal = KpiAppraisal::findOrFail($id);
        if (! in_array($appraisal->Status, ['Draft','Returned','Rejected','Submitted'], true)) {
            return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->withErrors([
                'status' => 'Only draft/returned/rejected/submitted appraisals can be updated.',
            ]);
        }

        $data = $request->validate([
            'Comments' => ['nullable','string','max:500'],
            'OverallRatingID' => ['required','exists:t_HRKPIRatingScales,Id'],
            'Items' => ['required','array','min:1'],
            'Items.*.GoalItemID' => ['required','exists:t_HRKPIGoalItems,Id'],
            'Items.*.ActualValue' => ['nullable','numeric'],
            'Items.*.AppraiseeComments' => ['nullable','string','max:255'],
            'Items.*.AppraiserComments' => ['nullable','string','max:255'],
            'Items.*.SelfRatingValue' => ['nullable','numeric','min:0'],
            'Items.*.SupervisorRatingValue' => ['nullable','numeric','min:0'],
        ]);

        $scale = KpiRatingScale::find($data['OverallRatingID']);
        $minScore = $scale ? (float)$scale->MinScore : 1.0;
        $maxScore = $scale ? (float)$scale->MaxScore : 5.0;
        $isSubmitting = $request->input('Action') === 'submit';
        $isSupervisorStage = $appraisal->Status === 'Submitted';
        $goalItems = $appraisal->goal?->items()->get()->keyBy('Id') ?? collect();
        $rawTotalScore = 0;

        $status = $appraisal->Status;
        if ($isSubmitting && $isSupervisorStage) {
            $status = 'SupervisorSubmitted';
        } elseif ($isSubmitting) {
            $status = 'Submitted';
        }
        $appraisal->update([
            'Status' => $status,
            'TotalScore' => $appraisal->TotalScore,
            'OverallRatingID' => $data['OverallRatingID'] ?? null,
            'AppraisedBy' => $isSubmitting && $isSupervisorStage ? auth()->id() : $appraisal->AppraisedBy,
            'AppraisedOn' => $isSubmitting && $isSupervisorStage ? now() : $appraisal->AppraisedOn,
            'Comments' => $data['Comments'] ?? null,
            'SubmittedBy' => $status === 'Submitted' ? auth()->id() : $appraisal->SubmittedBy,
            'SubmittedOn' => $status === 'Submitted' ? now() : $appraisal->SubmittedOn,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        KpiAppraisalItem::where('AppraisalID', $appraisal->Id)->delete();
        foreach ($data['Items'] as $item) {
            $selfRating = $item['SelfRatingValue'] ?? null;
            $supervisorRating = $item['SupervisorRatingValue'] ?? null;
            if ($isSubmitting && ! $isSupervisorStage && $selfRating === null) {
                throw ValidationException::withMessages([
                    'Items' => 'Self rating is required for all items before submitting.',
                ]);
            }
            if ($isSubmitting && $isSupervisorStage && $supervisorRating === null) {
                throw ValidationException::withMessages([
                    'Items' => 'Supervisor rating is required for all items before submitting.',
                ]);
            }
            if ($selfRating !== null && ($selfRating < $minScore || $selfRating > $maxScore)) {
                throw ValidationException::withMessages([
                    'Items' => 'Self rating must be within the selected overall rating scale.',
                ]);
            }
            if ($supervisorRating !== null && ($supervisorRating < $minScore || $supervisorRating > $maxScore)) {
                throw ValidationException::withMessages([
                    'Items' => 'Supervisor rating must be within the selected overall rating scale.',
                ]);
            }
            $weight = (float)($goalItems[$item['GoalItemID']]->Weight ?? 0);
            $normalizedWeight = $weight > 1 ? $weight / 100 : $weight;
            $selfScore = $selfRating !== null ? $normalizedWeight * (float)$selfRating : null;
            $finalScore = $supervisorRating !== null ? $normalizedWeight * (float)$supervisorRating : null;
            $rawTotalScore += (float)($finalScore ?? $selfScore ?? 0);
            KpiAppraisalItem::create([
                'AppraisalID' => $appraisal->Id,
                'GoalItemID' => $item['GoalItemID'],
                'ActualValue' => $item['ActualValue'] ?? null,
                'SelfRatingScaleID' => null,
                'SelfRatingValue' => $selfRating,
                'SelfScore' => $selfScore,
                'SupervisorRatingScaleID' => null,
                'SupervisorRatingValue' => $supervisorRating,
                'FinalScore' => $finalScore,
                'Score' => $finalScore ?? $selfScore,
                'RatingScaleID' => null,
                'AppraiseeComments' => $item['AppraiseeComments'] ?? null,
                'AppraiserComments' => $item['AppraiserComments'] ?? null,
                'Comments' => $item['AppraiserComments'] ?? null,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        $totalScore = $maxScore > 0 ? ($rawTotalScore / $maxScore) * 100 : $rawTotalScore;
        $appraisal->update([
            'TotalScore' => $totalScore,
        ]);

        return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->with('success', 'KPI appraisal updated.');
    }

    public function submit($id)
    {
        $appraisal = KpiAppraisal::findOrFail($id);
        if (! in_array($appraisal->Status, ['Draft','Returned','Rejected'], true)) {
            return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->withErrors([
                'status' => 'Only draft/returned/rejected appraisals can be submitted.',
            ]);
        }
        if (! $appraisal->OverallRatingID) {
            return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->withErrors([
                'status' => 'Overall rating is required before submitting.',
            ]);
        }
        $appraisal->update([
            'Status' => 'Submitted',
            'SubmittedBy' => auth()->id(),
            'SubmittedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->with('success', 'KPI appraisal submitted.');
    }

    public function approve($id)
    {
        $appraisal = KpiAppraisal::findOrFail($id);
        if ($appraisal->Status !== 'SupervisorSubmitted') {
            return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->withErrors([
                'status' => 'Only supervisor-submitted appraisals can be approved.',
            ]);
        }
        $appraisal->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->with('success', 'KPI appraisal approved.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'RejectionReason' => ['nullable','string','max:255'],
        ]);
        $appraisal = KpiAppraisal::findOrFail($id);
        $appraisal->update([
            'Status' => 'Rejected',
            'RejectedBy' => auth()->id(),
            'RejectedOn' => now(),
            'RejectionReason' => $data['RejectionReason'] ?? null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.kpi.appraisals.show', $appraisal->Id)->with('success', 'KPI appraisal rejected.');
    }
}
