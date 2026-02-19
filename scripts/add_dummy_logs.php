<?php

use Illuminate\Support\Facades\DB;
use App\Models\HR\AttendanceLog;
use App\Models\HR\AttendanceDaily;
use App\Models\HR\Employee;
use App\Models\HR\AttendanceDevice;
use App\Models\HR\Shift;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Get/Create Shift
$shift = Shift::firstOrCreate(
    ['Code' => 'GEN-01'],
    [
        'Name' => 'General Shift',
        'StartTime' => '08:00:00',
        'EndTime' => '17:00:00',
        'IsOvernight' => false,
        'GraceMinutes' => 15,
        'IsActive' => true,
        'CreatedBy' => 1,
        'CreatedOn' => now(),
    ]
);
echo "Using Shift: {$shift->Name}\n";

// 2. Get Employees
$employees = Employee::where('IsActive', true)->limit(10)->get();
if ($employees->isEmpty()) {
    echo "No employees found. Please create employees first.\n";
    exit;
}
echo "Found " . $employees->count() . " employees.\n";

// 3. Get Devices
$devices = AttendanceDevice::where('IsActive', true)->get();
if ($devices->isEmpty()) {
    // Determine devices ourselves if none found (fallback)
    $deviceList = [];
} else {
    $deviceList = $devices->pluck('Id')->toArray();
}

$channels = ['Biometric', 'RFID', 'Mobile', 'Web'];

$startDate = Carbon::now()->subDays(30);
$endDate = Carbon::now();

echo "Generating logs & daily summaries from {$startDate->toDateString()} to {$endDate->toDateString()}...\n";

$logsCount = 0;
$dailyCount = 0;

foreach ($employees as $employee) {
    echo "Processing Employee: {$employee->FirstName} {$employee->LastName} ({$employee->EmployeeNo})\n";
    
    // Iterate through days
    $current = $startDate->copy();
    while ($current <= $endDate) {
        // Skip weekends
        if ($current->isWeekend()) {
            $current->addDay();
            continue;
        }

        $workDate = $current->format('Y-m-d');

        // Check availability (80% present)
        $isPresent = (rand(1, 100) <= 80);

        if ($isPresent) {
            // Generate distinct times
            // IN: 07:30 - 09:30
            $inHour = rand(7, 9);
            $inMin = rand(0, 59);
            if ($inHour == 7 && $inMin < 30) $inMin = 30; // Min 7:30
            
            $inTime = $current->copy()->setTime($inHour, $inMin, 0);

            // OUT: 16:30 - 19:00
            $outHour = rand(16, 19);
            $outMin = rand(0, 59);
            if ($outHour == 16 && $outMin < 30) $outMin = 30; // Min 16:30

            $outTime = $current->copy()->setTime($outHour, $outMin, 0);

            // Calculate hours
            $diffInMinutes = $inTime->diffInMinutes($outTime);
            $totalHours = round($diffInMinutes / 60, 2);

            // 1. Create Raw Logs
            $deviceId = !empty($deviceList) ? $deviceList[array_rand($deviceList)] : null;
            $channel = $deviceId ? AttendanceDevice::find($deviceId)->Channel : $channels[array_rand($channels)];

            // IN
            AttendanceLog::create([
                'EmployeeID' => $employee->Id,
                'LogType' => 'IN',
                'LogTime' => $inTime,
                'Channel' => $channel,
                'DeviceID' => $deviceId,
                'IsProcessed' => 1, // Marked as processed since creating daily entry
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ]);
            $logsCount++;

            // OUT
            AttendanceLog::create([
                'EmployeeID' => $employee->Id,
                'LogType' => 'OUT',
                'LogTime' => $outTime,
                'Channel' => $channel,
                'DeviceID' => $deviceId,
                'IsProcessed' => 1,
                'CreatedBy' => 1,
                'CreatedOn' => now(),
            ]);
            $logsCount++;

            // 2. Create Daily Summary
            // Check existing
            $exists = AttendanceDaily::where('EmployeeID', $employee->Id)->where('WorkDate', $workDate)->exists();
            if (!$exists) {
                AttendanceDaily::create([
                    'EmployeeID' => $employee->Id,
                    'WorkDate' => $workDate,
                    'ShiftID' => $shift->Id,
                    'FirstInTime' => $inTime,
                    'LastOutTime' => $outTime,
                    'TotalHours' => $totalHours,
                    'OvertimeHours' => ($totalHours > 9) ? ($totalHours - 9) : 0,
                    'Status' => 'Present',
                    'LateMinutes' => ($inTime->format('H:i') > '08:15') ? abs($inTime->diffInMinutes($current->copy()->setTime(8, 0, 0))) : 0,
                    'EarlyExitMinutes' => 0,
                    'IsManualAdjusted' => false,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                ]);
                $dailyCount++;
            }

        } else {
            // Absent Entry
            $exists = AttendanceDaily::where('EmployeeID', $employee->Id)->where('WorkDate', $workDate)->exists();
            if (!$exists) {
                AttendanceDaily::create([
                    'EmployeeID' => $employee->Id,
                    'WorkDate' => $workDate,
                    'ShiftID' => $shift->Id,
                    'FirstInTime' => null,
                    'LastOutTime' => null,
                    'TotalHours' => 0,
                    'OvertimeHours' => 0,
                    'Status' => 'Absent',
                    'LateMinutes' => 0,
                    'EarlyExitMinutes' => 0,
                    'IsManualAdjusted' => false,
                    'CreatedBy' => 1,
                    'CreatedOn' => now(),
                ]);
                $dailyCount++;
            }
        }

        $current->addDay();
    }
}

echo "Done. Added $logsCount raw logs and $dailyCount daily summaries.\n";
