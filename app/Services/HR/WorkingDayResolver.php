<?php

namespace App\Services\HR;

use App\Models\HR\EmployeeWorkingDaySetting;
use App\Models\HR\WorkingDaySetting;
use Carbon\Carbon;

class WorkingDayResolver
{
    private array $workingMapCache = [];

    public function getWorkingMap(?int $employeeId = null): array
    {
        $cacheKey = $employeeId ?? 0;
        if (isset($this->workingMapCache[$cacheKey])) {
            return $this->workingMapCache[$cacheKey];
        }

        $defaults = WorkingDaySetting::all()->keyBy('DayOfWeek');
        $overrides = collect();
        if ($employeeId) {
            $overrides = EmployeeWorkingDaySetting::where('EmployeeID', $employeeId)
                ->get()
                ->keyBy('DayOfWeek');
        }

        $map = [];
        foreach (range(0, 6) as $dow) {
            $rec = $overrides[$dow] ?? $defaults[$dow] ?? null;
            $defaultWorking = ($dow >= 1 && $dow <= 5);
            $workingFlag = $rec ? (bool)$rec->IsWorking : $defaultWorking;
            $fraction = $rec
                ? (float)($rec->DayFraction ?? ($workingFlag ? 1.0 : 0.0))
                : ($workingFlag ? 1.0 : 0.0);
            if ($workingFlag && $fraction <= 0) {
                $fraction = 0.5;
            }

            $hours = 0.0;
            if ($workingFlag) {
                $hours = $this->hoursFromTimes($rec?->StartTime, $rec?->EndTime);
                if ($hours <= 0) {
                    $hours = 8.0;
                }
                $hours = $hours * $fraction;
            }

            $map[$dow] = [
                'working' => $workingFlag,
                'fraction' => $fraction,
                'hours' => $hours,
            ];
        }

        $this->workingMapCache[$cacheKey] = $map;
        return $map;
    }

    private function hoursFromTimes($start, $end): float
    {
        $start = $this->normalizeTime($start);
        $end = $this->normalizeTime($end);
        if (!$start || !$end) {
            return 0.0;
        }
        $startTime = Carbon::createFromFormat('H:i:s', $start);
        $endTime = Carbon::createFromFormat('H:i:s', $end);
        $minutes = $endTime->diffInMinutes($startTime, false);
        if ($minutes <= 0) {
            return 0.0;
        }
        return round($minutes / 60, 2);
    }

    private function normalizeTime($time): ?string
    {
        if ($time === null) {
            return null;
        }
        $value = trim((string)$time);
        if ($value === '') {
            return null;
        }
        $value = explode('.', $value)[0];
        return $value;
    }
}
