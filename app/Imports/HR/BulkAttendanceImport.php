<?php

namespace App\Imports\HR;

use App\Models\HR\AttendanceLog;
use App\Models\HR\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;

class BulkAttendanceImport implements OnEachRow, WithHeadingRow
{
    use ImportHelper;

    private int $processed = 0;
    private int $created = 0;
    private int $skipped = 0;

    public function onRow(Row $row): void
    {
        $this->processed++;

        $data = $row->toArray();
        $data = array_change_key_case($data, CASE_LOWER);
        $data = array_map([$this, 'cleanValue'], $data);

        $employeeNo = $data['employeeno'] ?? $data['employee_no'] ?? null;
        $logTime = $this->parseDateTime($data['logtime'] ?? $data['log_time'] ?? $data['timestamp'] ?? null);
        $logType = $data['logtype'] ?? $data['log_type'] ?? null;

        if (!$employeeNo || !$logTime || !$logType) {
            $this->skipped++;
            return;
        }

        $employee = Employee::where('EmployeeNo', $employeeNo)->first();
        if (!$employee) {
            $this->skipped++;
            Log::warning('Attendance import skipped: employee not found.', ['employee_no' => $employeeNo]);
            return;
        }

        $logType = $this->normalizeLogType($logType);
        $channel = $data['channel'] ?? $data['source'] ?? null;
        $deviceId = $data['deviceid'] ?? $data['device_id'] ?? null;

        $exists = AttendanceLog::where('EmployeeID', $employee->Id)
            ->where('LogTime', $logTime)
            ->where('LogType', $logType)
            ->where('Channel', $channel)
            ->where('DeviceID', $deviceId)
            ->exists();

        if ($exists) {
            $this->skipped++;
            return;
        }

        AttendanceLog::create([
            'EmployeeID' => $employee->Id,
            'LogType' => $logType,
            'LogTime' => $logTime,
            'Channel' => $channel,
            'DeviceID' => $deviceId,
            'Latitude' => $data['latitude'] ?? null,
            'Longitude' => $data['longitude'] ?? null,
            'IsProcessed' => 0,
            'Remarks' => $data['remarks'] ?? null,
            'CreatedBy' => Auth::id() ?? 1,
            'CreatedOn' => now(),
        ]);

        $this->created++;
    }

    private function normalizeLogType($value): string
    {
        $value = strtolower(trim((string)$value));
        if (in_array($value, ['in', 'clockin', 'checkin', 'clock-in', 'check-in'], true)) {
            return 'IN';
        }
        if (in_array($value, ['out', 'clockout', 'checkout', 'clock-out', 'check-out'], true)) {
            return 'OUT';
        }
        return strtoupper($value);
    }

    public function getProcessedCount(): int
    {
        return $this->processed;
    }

    public function getCreatedCount(): int
    {
        return $this->created;
    }

    public function getSkippedCount(): int
    {
        return $this->skipped;
    }
}
