<?php

namespace App\Imports\HR;

use App\Models\HR\Employee;
use App\Models\HR\KpiGoal;
use App\Models\HR\KpiGoalItem;
use App\Models\HR\KpiItem;
use App\Models\HR\KpiPeriod;
use App\Models\HR\KpiPerspectiveWeight;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BulkKpiTargetsImport implements ToCollection, WithHeadingRow
{
    use ImportHelper;

    private int $processed = 0;
    private int $created = 0;
    private int $updated = 0;
    private int $skipped = 0;

    public function collection(Collection $rows): void
    {
        $groups = [];

        foreach ($rows as $row) {
            $this->processed++;
            $rowArray = is_array($row) ? $row : $row->toArray();
            $data = array_change_key_case($rowArray, CASE_LOWER);
            $data = array_map([$this, 'cleanValue'], $data);

            $employeeNo = $data['employeeno'] ?? $data['employee_no'] ?? null;
            $periodValue = $data['period'] ?? $data['periodcode'] ?? $data['period_code'] ?? null;
            $year = $data['year'] ?? null;
            $segment = $this->parseSegment($data['segment'] ?? $data['periodsegment'] ?? $data['period_segment'] ?? null) ?? 1;
            $kpiCode = $data['kpicode'] ?? $data['kpi_code'] ?? null;
            $kpiName = $data['kpiname'] ?? $data['kpi_name'] ?? null;
            $weight = $data['weight'] ?? null;

            if (!$employeeNo || !$periodValue || !$year || (!$kpiCode && !$kpiName) || $weight === null) {
                $this->skipped++;
                continue;
            }

            $employee = Employee::where('EmployeeNo', $employeeNo)->first();
            if (!$employee) {
                $this->skipped++;
                continue;
            }

            $period = KpiPeriod::where('IsActive', 1)
                ->where(function ($q) use ($periodValue) {
                    $q->where('Name', $periodValue)->orWhere('Code', $periodValue);
                })
                ->first();
            if (!$period) {
                $this->skipped++;
                continue;
            }

            $segmentCount = $this->segmentCount($period);
            if ($segment < 1 || $segment > $segmentCount) {
                $this->skipped++;
                continue;
            }

            $kpiItem = KpiItem::where('IsActive', 1)
                ->when($kpiCode, function ($q) use ($kpiCode) {
                    $q->where('Code', $kpiCode);
                })
                ->when(!$kpiCode && $kpiName, function ($q) use ($kpiName) {
                    $q->where('Name', $kpiName);
                })
                ->first();

            if (!$kpiItem) {
                $this->skipped++;
                continue;
            }

            $weights = KpiPerspectiveWeight::resolveWeights($period->Id, $employee->GradeID, $employee->RoleID);
            if ($weights->isNotEmpty() && !$weights->has($kpiItem->PerspectiveID)) {
                $this->skipped++;
                continue;
            }

            $key = implode('|', [$employee->Id, $period->Id, (int)$year, $segment]);
            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'employee' => $employee,
                    'period' => $period,
                    'year' => (int)$year,
                    'segment' => $segment,
                    'notes' => $data['notes'] ?? null,
                    'items' => [],
                ];
            }

            $groups[$key]['items'][] = [
                'KpiItemID' => $kpiItem->Id,
                'AnnualTarget' => $data['annualtarget'] ?? $data['annual_target'] ?? null,
                'PeriodTarget' => $data['periodtarget'] ?? $data['period_target'] ?? null,
                'TargetValue' => $data['targetvalue'] ?? $data['target_value'] ?? null,
                'Weight' => $weight,
                'Notes' => $data['itemnotes'] ?? $data['item_notes'] ?? null,
            ];
        }

        $userId = Auth::id() ?? 1;

        foreach ($groups as $group) {
            $goal = KpiGoal::where('EmployeeID', $group['employee']->Id)
                ->where('PeriodID', $group['period']->Id)
                ->where('PeriodYear', $group['year'])
                ->where('PeriodSegment', $group['segment'])
                ->first();

            if ($goal && !in_array($goal->Status, ['Draft', 'Returned', 'Rejected'], true)) {
                $this->skipped++;
                continue;
            }

            if (!$goal) {
                $goal = KpiGoal::create([
                    'EmployeeID' => $group['employee']->Id,
                    'PeriodID' => $group['period']->Id,
                    'PeriodYear' => $group['year'],
                    'PeriodSegment' => $group['segment'],
                    'Status' => 'Draft',
                    'TotalWeight' => 0,
                    'Notes' => $group['notes'],
                    'CreatedBy' => $userId,
                    'CreatedOn' => now(),
                ]);
                $this->created++;
            } else {
                $goal->update([
                    'Notes' => $group['notes'] ?? $goal->Notes,
                    'ModifiedBy' => $userId,
                    'ModifiedOn' => now(),
                ]);
                $this->updated++;
            }

            foreach ($group['items'] as $item) {
                $existing = KpiGoalItem::where('GoalID', $goal->Id)
                    ->where('KpiItemID', $item['KpiItemID'])
                    ->first();

                $payload = [
                    'GoalID' => $goal->Id,
                    'KpiItemID' => $item['KpiItemID'],
                    'AnnualTarget' => $item['AnnualTarget'],
                    'PeriodTarget' => $item['PeriodTarget'],
                    'TargetValue' => $item['TargetValue'],
                    'Weight' => $item['Weight'] ?? 0,
                    'Notes' => $item['Notes'],
                ];

                if ($existing) {
                    $payload['ModifiedBy'] = $userId;
                    $payload['ModifiedOn'] = now();
                    $existing->update($payload);
                } else {
                    $payload['CreatedBy'] = $userId;
                    $payload['CreatedOn'] = now();
                    KpiGoalItem::create($payload);
                }
            }

            $totalWeight = KpiGoalItem::where('GoalID', $goal->Id)->sum('Weight');
            $goal->update([
                'TotalWeight' => $totalWeight,
                'ModifiedBy' => $userId,
                'ModifiedOn' => now(),
            ]);
        }
    }

    private function segmentCount(KpiPeriod $period): int
    {
        $start = (int)($period->StartMonth ?? 1);
        $end = (int)($period->EndMonth ?? 12);
        $length = $end - $start + 1;
        if ($length <= 0 || 12 % $length !== 0) {
            return 1;
        }
        return (int)(12 / $length);
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
    }

    public function getCreatedCount(): int
    {
        return $this->created;
    }

    public function getUpdatedCount(): int
    {
        return $this->updated;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}
