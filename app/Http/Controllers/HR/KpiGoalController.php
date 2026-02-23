<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\KpiGoal;
use App\Models\HR\KpiGoalItem;
use App\Models\HR\KpiItem;
use App\Models\HR\KpiPeriod;
use App\Models\HR\KpiPerspective;
use App\Models\HR\KpiPerspectiveWeight;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class KpiGoalController extends Controller
{
    private function periodSegmentCount(?KpiPeriod $period): int
    {
        if (! $period) {
            return 1;
        }
        $start = (int)($period->StartMonth ?? 1);
        $end = (int)($period->EndMonth ?? 12);
        $length = $end - $start + 1;
        if ($length <= 0) {
            return 1;
        }
        if (12 % $length !== 0) {
            return 1;
        }

        return (int)(12 / $length);
    }

    private function buildPeriodsPayload($periods)
    {
        return $periods->map(function ($period) {
            return [
                'Id' => $period->Id,
                'Name' => $period->Name,
                'Code' => $period->Code,
                'StartMonth' => $period->StartMonth,
                'EndMonth' => $period->EndMonth,
                'SegmentCount' => $this->periodSegmentCount($period),
            ];
        })->values();
    }

    private function buildYearOptions(int $startYear, int $years = 6): array
    {
        $options = [];
        for ($i = 0; $i < $years; $i++) {
            $options[] = $startYear + $i;
        }

        return $options;
    }

    public function index(Request $request)
    {
        $query = KpiGoal::with(['employee','period'])->orderByDesc('Id');

        if ($request->filled('status')) {
            $query->where('Status', $request->status);
        }
        if ($request->filled('period_id')) {
            $query->where('PeriodID', (int)$request->period_id);
        }
        if ($request->filled('employee_id')) {
            $query->where('EmployeeID', (int)$request->employee_id);
        }

        $goals = $query->paginate(30);
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName','EmployeeNo']);
        $periods = KpiPeriod::where('IsActive', 1)->orderBy('Name')->get(['Id','Name','Code','StartMonth','EndMonth']);

        return view('hr.kpi.goals.index', compact('goals', 'employees', 'periods'));
    }

    public function create()
    {
        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName','EmployeeNo','GradeID','RoleID']);
        $periods = KpiPeriod::where('IsActive', 1)->orderBy('Name')->get(['Id','Name','Code','StartMonth','EndMonth']);
        $items = KpiItem::where('IsActive', 1)->orderBy('Name')->get(['Id','Code','Name','PerspectiveID']);
        $perspectives = KpiPerspective::where('IsActive', 1)->orderBy('Name')->get(['Id','Name']);
        $perspectiveWeights = KpiPerspectiveWeight::where('IsActive', 1)->get(['PerspectiveID','PeriodID','GradeID','RoleID','Weight']);
        $kpiItemsPayload = $items->map(function ($item) {
            return [
                'Id' => $item->Id,
                'Name' => $item->Name,
                'PerspectiveID' => $item->PerspectiveID,
            ];
        })->values();
        $perspectivesPayload = $perspectives->map(function ($perspective) {
            return [
                'Id' => $perspective->Id,
                'Name' => $perspective->Name,
            ];
        })->values();
        $perspectiveWeightsPayload = $perspectiveWeights->map(function ($weight) {
            return [
                'PerspectiveID' => $weight->PerspectiveID,
                'PeriodID' => $weight->PeriodID,
                'GradeID' => $weight->GradeID,
                'RoleID' => $weight->RoleID,
                'Weight' => $weight->Weight,
            ];
        })->values();
        $periodsPayload = $this->buildPeriodsPayload($periods);
        $yearOptions = $this->buildYearOptions((int)now()->year, 7);

        return view('hr.kpi.goals.create', compact(
            'employees',
            'periods',
            'items',
            'perspectives',
            'perspectiveWeights',
            'kpiItemsPayload',
            'perspectivesPayload',
            'perspectiveWeightsPayload',
            'periodsPayload',
            'yearOptions'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'PeriodID' => ['required','exists:t_HRKPIPeriods,Id'],
            'PeriodYear' => ['required','integer','min:2000','max:2100'],
            'PeriodSegment' => ['nullable','integer','min:1','max:12'],
            'Notes' => ['nullable','string','max:500'],
            'Items' => ['required','array','min:1'],
            'Items.*.KpiItemID' => ['required','exists:t_HRKPIItems,Id'],
            'Items.*.PerspectiveID' => ['nullable','integer','exists:t_HRKPIPerspectives,Id'],
            'Items.*.AnnualTarget' => ['nullable','numeric'],
            'Items.*.PeriodTarget' => ['nullable','numeric'],
            'Items.*.TargetValue' => ['nullable','numeric'],
            'Items.*.Weight' => ['required','numeric','min:0'],
            'Items.*.Notes' => ['nullable','string','max:255'],
        ]);

        $period = KpiPeriod::find($data['PeriodID']);
        $segmentCount = $this->periodSegmentCount($period);
        $segment = $data['PeriodSegment'] ?? 1;
        if ($segment < 1 || $segment > $segmentCount) {
            throw ValidationException::withMessages([
                'PeriodSegment' => 'Invalid period segment for the selected period.',
            ]);
        }

        $employee = Employee::find($data['EmployeeID']);
        $weightMap = KpiPerspectiveWeight::resolveWeights(
            $data['PeriodID'],
            $employee?->GradeID,
            $employee?->RoleID
        );
        $allowedPerspectives = $weightMap->keys()->map(fn ($id) => (int)$id)->values();
        if ($weightMap->isEmpty()) {
            throw ValidationException::withMessages([
                'PeriodID' => 'No perspectives are configured for this period/grade/role.',
            ]);
        }
        $perspectiveWeights = $weightMap->mapWithKeys(function ($row, $perspectiveId) {
            $weight = (float)$row->Weight;
            $percent = $weight <= 1 ? $weight * 100 : $weight;

            return [(int)$perspectiveId => $percent];
        })->toArray();
        $perspectiveNames = KpiPerspective::whereIn('Id', array_keys($perspectiveWeights))
            ->pluck('Name', 'Id')
            ->toArray();
        $totalPerspectiveWeight = array_sum($perspectiveWeights);
        if (abs($totalPerspectiveWeight - 100) > 0.01) {
            throw ValidationException::withMessages([
                'Items' => 'Perspective weights must total 100 for the selected period/grade/role.',
            ]);
        }

        $existing = KpiGoal::where('EmployeeID', $data['EmployeeID'])
            ->where('PeriodID', $data['PeriodID'])
            ->where('PeriodYear', $data['PeriodYear'])
            ->where('PeriodSegment', $segment)
            ->first();
        if ($existing) {
            throw ValidationException::withMessages([
                'EmployeeID' => 'A KPI goal set already exists for this employee and period.',
            ]);
        }

        $totalWeight = collect($data['Items'])->sum(function ($item) {
            return (float)$item['Weight'];
        });

        $status = $request->input('Action') === 'submit' ? 'Submitted' : 'Draft';
        if ($status === 'Submitted' && round($totalWeight, 2) !== 100.00) {
            throw ValidationException::withMessages([
                'Items' => 'Total weight must be 100 before submitting.',
            ]);
        }

        $itemWeightTotals = [];
        foreach ($data['Items'] as $item) {
            $kpiItem = KpiItem::find($item['KpiItemID']);
            $itemPerspectiveId = $item['PerspectiveID'] ?? $kpiItem?->PerspectiveID;
            if (! $itemPerspectiveId) {
                throw ValidationException::withMessages([
                    'Items' => 'Each KPI item must have a perspective assigned.',
                ]);
            }
            if ($itemPerspectiveId && $kpiItem?->PerspectiveID && (int)$itemPerspectiveId !== (int)$kpiItem->PerspectiveID) {
                throw ValidationException::withMessages([
                    'Items' => 'KPI item does not match the selected perspective.',
                ]);
            }
            if (! in_array((int)$itemPerspectiveId, $allowedPerspectives->toArray(), true)) {
                throw ValidationException::withMessages([
                    'Items' => 'One or more KPI perspectives are not allowed for this employee.',
                ]);
            }
            $itemWeightTotals[(int)$itemPerspectiveId] = ($itemWeightTotals[(int)$itemPerspectiveId] ?? 0) + (float)$item['Weight'];
        }

        foreach ($perspectiveWeights as $perspectiveId => $expected) {
            $actual = $itemWeightTotals[$perspectiveId] ?? 0;
            if (abs($actual - $expected) > 0.01) {
                $name = $perspectiveNames[$perspectiveId] ?? "Perspective {$perspectiveId}";

                throw ValidationException::withMessages([
                    'Items' => "Total KPI weight for {$name} must be {$expected} (currently {$actual}).",
                ]);
            }
        }
        $goal = KpiGoal::create([
            'EmployeeID' => $data['EmployeeID'],
            'PeriodID' => $data['PeriodID'],
            'PeriodYear' => $data['PeriodYear'],
            'PeriodSegment' => $segment,
            'Status' => $status,
            'TotalWeight' => $totalWeight,
            'Notes' => $data['Notes'] ?? null,
            'SubmittedBy' => $status === 'Submitted' ? auth()->id() : null,
            'SubmittedOn' => $status === 'Submitted' ? now() : null,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);

        foreach ($data['Items'] as $item) {
            $kpiItem = KpiItem::find($item['KpiItemID']);
            $itemPerspectiveId = $item['PerspectiveID'] ?? $kpiItem?->PerspectiveID;
            $periodTarget = $item['PeriodTarget'] ?? $item['TargetValue'] ?? null;
            KpiGoalItem::create([
                'GoalID' => $goal->Id,
                'KpiItemID' => $item['KpiItemID'],
                'AnnualTarget' => $item['AnnualTarget'] ?? null,
                'PeriodTarget' => $periodTarget,
                'TargetValue' => $periodTarget,
                'Weight' => $item['Weight'],
                'Notes' => $item['Notes'] ?? null,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        return redirect()->route('hr.kpi.goals.show', $goal->Id)->with('success', 'KPI goals saved.');
    }

    public function show($id)
    {
        $goal = KpiGoal::with(['employee','period','items.kpiItem.perspective'])->findOrFail($id);
        $gradeId = $goal->employee?->GradeID;
        $roleId = $goal->employee?->RoleID;
        $weightMap = KpiPerspectiveWeight::resolveWeights($goal->PeriodID, $gradeId, $roleId);
        $perspectiveIds = $goal->items->pluck('kpiItem.PerspectiveID')->filter()->unique()->values();
        $perspectives = KpiPerspective::whereIn('Id', $perspectiveIds)->get()->keyBy('Id');
        $perspectiveGroups = $goal->items->groupBy(function ($item) {
            return $item->kpiItem?->PerspectiveID ?? 0;
        });

        return view('hr.kpi.goals.show', compact('goal', 'perspectiveGroups', 'perspectives', 'weightMap'));
    }

    public function edit($id)
    {
        $goal = KpiGoal::with(['items.kpiItem'])->findOrFail($id);
        if (! in_array($goal->Status, ['Draft','Returned','Rejected'], true)) {
            return redirect()->route('hr.kpi.goals.show', $goal->Id)->withErrors([
                'status' => 'Only draft/returned/rejected goals can be edited.',
            ]);
        }

        $employees = Employee::orderBy('FirstName')->get(['Id','FirstName','LastName','EmployeeNo','GradeID','RoleID']);
        $periods = KpiPeriod::where('IsActive', 1)->orderBy('Name')->get(['Id','Name']);
        $items = KpiItem::where('IsActive', 1)->orderBy('Name')->get(['Id','Code','Name','PerspectiveID']);
        $perspectives = KpiPerspective::where('IsActive', 1)->orderBy('Name')->get(['Id','Name']);
        $perspectiveWeights = KpiPerspectiveWeight::where('IsActive', 1)->get(['PerspectiveID','PeriodID','GradeID','RoleID','Weight']);
        $kpiItemsPayload = $items->map(function ($item) {
            return [
                'Id' => $item->Id,
                'Name' => $item->Name,
                'PerspectiveID' => $item->PerspectiveID,
            ];
        })->values();
        $perspectivesPayload = $perspectives->map(function ($perspective) {
            return [
                'Id' => $perspective->Id,
                'Name' => $perspective->Name,
            ];
        })->values();
        $perspectiveWeightsPayload = $perspectiveWeights->map(function ($weight) {
            return [
                'PerspectiveID' => $weight->PerspectiveID,
                'PeriodID' => $weight->PeriodID,
                'GradeID' => $weight->GradeID,
                'RoleID' => $weight->RoleID,
                'Weight' => $weight->Weight,
            ];
        })->values();
        $periodsPayload = $this->buildPeriodsPayload($periods);
        $yearOptions = $this->buildYearOptions((int)now()->year, 7);

        return view('hr.kpi.goals.edit', compact(
            'goal',
            'employees',
            'periods',
            'items',
            'perspectives',
            'perspectiveWeights',
            'kpiItemsPayload',
            'perspectivesPayload',
            'perspectiveWeightsPayload',
            'periodsPayload',
            'yearOptions'
        ));
    }

    public function update(Request $request, $id)
    {
        $goal = KpiGoal::findOrFail($id);
        if (! in_array($goal->Status, ['Draft','Returned','Rejected'], true)) {
            return redirect()->route('hr.kpi.goals.show', $goal->Id)->withErrors([
                'status' => 'Only draft/returned/rejected goals can be updated.',
            ]);
        }

        $data = $request->validate([
            'EmployeeID' => ['required','exists:t_HREmployees,Id'],
            'PeriodID' => ['required','exists:t_HRKPIPeriods,Id'],
            'PeriodYear' => ['required','integer','min:2000','max:2100'],
            'PeriodSegment' => ['nullable','integer','min:1','max:12'],
            'Notes' => ['nullable','string','max:500'],
            'Items' => ['required','array','min:1'],
            'Items.*.KpiItemID' => ['required','exists:t_HRKPIItems,Id'],
            'Items.*.PerspectiveID' => ['nullable','integer','exists:t_HRKPIPerspectives,Id'],
            'Items.*.AnnualTarget' => ['nullable','numeric'],
            'Items.*.PeriodTarget' => ['nullable','numeric'],
            'Items.*.TargetValue' => ['nullable','numeric'],
            'Items.*.Weight' => ['required','numeric','min:0'],
            'Items.*.Notes' => ['nullable','string','max:255'],
        ]);

        $period = KpiPeriod::find($data['PeriodID']);
        $segmentCount = $this->periodSegmentCount($period);
        $segment = $data['PeriodSegment'] ?? 1;
        if ($segment < 1 || $segment > $segmentCount) {
            throw ValidationException::withMessages([
                'PeriodSegment' => 'Invalid period segment for the selected period.',
            ]);
        }

        $employee = Employee::find($data['EmployeeID']);
        $weightMap = KpiPerspectiveWeight::resolveWeights(
            $data['PeriodID'],
            $employee?->GradeID,
            $employee?->RoleID
        );
        $allowedPerspectives = $weightMap->keys()->map(fn ($id) => (int)$id)->values();
        if ($weightMap->isEmpty()) {
            throw ValidationException::withMessages([
                'PeriodID' => 'No perspectives are configured for this period/grade/role.',
            ]);
        }
        $perspectiveWeights = $weightMap->mapWithKeys(function ($row, $perspectiveId) {
            $weight = (float)$row->Weight;
            $percent = $weight <= 1 ? $weight * 100 : $weight;

            return [(int)$perspectiveId => $percent];
        })->toArray();
        $perspectiveNames = KpiPerspective::whereIn('Id', array_keys($perspectiveWeights))
            ->pluck('Name', 'Id')
            ->toArray();
        $totalPerspectiveWeight = array_sum($perspectiveWeights);
        if (abs($totalPerspectiveWeight - 100) > 0.01) {
            throw ValidationException::withMessages([
                'Items' => 'Perspective weights must total 100 for the selected period/grade/role.',
            ]);
        }

        $existing = KpiGoal::where('EmployeeID', $data['EmployeeID'])
            ->where('PeriodID', $data['PeriodID'])
            ->where('PeriodYear', $data['PeriodYear'])
            ->where('PeriodSegment', $segment)
            ->where('Id', '<>', $goal->Id)
            ->first();
        if ($existing) {
            throw ValidationException::withMessages([
                'EmployeeID' => 'A KPI goal set already exists for this employee and period.',
            ]);
        }

        $totalWeight = collect($data['Items'])->sum(function ($item) {
            return (float)$item['Weight'];
        });

        $status = $request->input('Action') === 'submit' ? 'Submitted' : $goal->Status;
        if ($status === 'Submitted' && round($totalWeight, 2) !== 100.00) {
            throw ValidationException::withMessages([
                'Items' => 'Total weight must be 100 before submitting.',
            ]);
        }

        $itemWeightTotals = [];
        foreach ($data['Items'] as $item) {
            $kpiItem = KpiItem::find($item['KpiItemID']);
            $itemPerspectiveId = $item['PerspectiveID'] ?? $kpiItem?->PerspectiveID;
            if (! $itemPerspectiveId) {
                throw ValidationException::withMessages([
                    'Items' => 'Each KPI item must have a perspective assigned.',
                ]);
            }
            if ($itemPerspectiveId && $kpiItem?->PerspectiveID && (int)$itemPerspectiveId !== (int)$kpiItem->PerspectiveID) {
                throw ValidationException::withMessages([
                    'Items' => 'KPI item does not match the selected perspective.',
                ]);
            }
            if (! in_array((int)$itemPerspectiveId, $allowedPerspectives->toArray(), true)) {
                throw ValidationException::withMessages([
                    'Items' => 'One or more KPI perspectives are not allowed for this employee.',
                ]);
            }
            $itemWeightTotals[(int)$itemPerspectiveId] = ($itemWeightTotals[(int)$itemPerspectiveId] ?? 0) + (float)$item['Weight'];
        }

        foreach ($perspectiveWeights as $perspectiveId => $expected) {
            $actual = $itemWeightTotals[$perspectiveId] ?? 0;
            if (abs($actual - $expected) > 0.01) {
                $name = $perspectiveNames[$perspectiveId] ?? "Perspective {$perspectiveId}";

                throw ValidationException::withMessages([
                    'Items' => "Total KPI weight for {$name} must be {$expected} (currently {$actual}).",
                ]);
            }
        }
        $goal->update([
            'EmployeeID' => $data['EmployeeID'],
            'PeriodID' => $data['PeriodID'],
            'PeriodYear' => $data['PeriodYear'],
            'PeriodSegment' => $segment,
            'Status' => $status,
            'TotalWeight' => $totalWeight,
            'Notes' => $data['Notes'] ?? null,
            'SubmittedBy' => $status === 'Submitted' ? auth()->id() : $goal->SubmittedBy,
            'SubmittedOn' => $status === 'Submitted' ? now() : $goal->SubmittedOn,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        KpiGoalItem::where('GoalID', $goal->Id)->delete();
        foreach ($data['Items'] as $item) {
            $kpiItem = KpiItem::find($item['KpiItemID']);
            $itemPerspectiveId = $item['PerspectiveID'] ?? $kpiItem?->PerspectiveID;
            $periodTarget = $item['PeriodTarget'] ?? $item['TargetValue'] ?? null;
            KpiGoalItem::create([
                'GoalID' => $goal->Id,
                'KpiItemID' => $item['KpiItemID'],
                'AnnualTarget' => $item['AnnualTarget'] ?? null,
                'PeriodTarget' => $periodTarget,
                'TargetValue' => $periodTarget,
                'Weight' => $item['Weight'],
                'Notes' => $item['Notes'] ?? null,
                'CreatedBy' => auth()->id(),
                'CreatedOn' => now(),
            ]);
        }

        return redirect()->route('hr.kpi.goals.show', $goal->Id)->with('success', 'KPI goals updated.');
    }

    public function submit($id)
    {
        $goal = KpiGoal::findOrFail($id);
        if (! in_array($goal->Status, ['Draft','Returned','Rejected'], true)) {
            return redirect()->route('hr.kpi.goals.show', $goal->Id)->withErrors([
                'status' => 'Only draft/returned/rejected goals can be submitted.',
            ]);
        }
        $goal->update([
            'Status' => 'Submitted',
            'SubmittedBy' => auth()->id(),
            'SubmittedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.kpi.goals.show', $goal->Id)->with('success', 'KPI goals submitted.');
    }

    public function approve($id)
    {
        $goal = KpiGoal::findOrFail($id);
        if ($goal->Status !== 'Submitted') {
            return redirect()->route('hr.kpi.goals.show', $goal->Id)->withErrors([
                'status' => 'Only submitted goals can be approved.',
            ]);
        }
        $goal->update([
            'Status' => 'Approved',
            'ApprovedBy' => auth()->id(),
            'ApprovedOn' => now(),
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.kpi.goals.show', $goal->Id)->with('success', 'KPI goals approved.');
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'RejectionReason' => ['nullable','string','max:255'],
        ]);
        $goal = KpiGoal::findOrFail($id);
        $goal->update([
            'Status' => 'Rejected',
            'RejectedBy' => auth()->id(),
            'RejectedOn' => now(),
            'RejectionReason' => $data['RejectionReason'] ?? null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.kpi.goals.show', $goal->Id)->with('success', 'KPI goals rejected.');
    }

    public function return(Request $request, $id)
    {
        $data = $request->validate([
            'RejectionReason' => ['nullable','string','max:255'],
        ]);
        $goal = KpiGoal::findOrFail($id);
        $goal->update([
            'Status' => 'Returned',
            'RejectionReason' => $data['RejectionReason'] ?? null,
            'ModifiedBy' => auth()->id(),
            'ModifiedOn' => now(),
        ]);

        return redirect()->route('hr.kpi.goals.show', $goal->Id)->with('success', 'KPI goals returned for updates.');
    }
}
