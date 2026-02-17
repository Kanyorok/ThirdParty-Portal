<?php

use Illuminate\Support\Facades\DB;
use App\Models\HR\AttendanceDaily;
use App\Models\HR\OvertimeRequest;
use App\Models\HR\AttendanceException;
use App\Models\HR\OvertimeRate;
use App\Models\HR\JobGrade;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding Overtime Rates & Exceptions...\n";

// 1. Ensure Job Grade exists
$grade = JobGrade::firstOrCreate(
    ['Code' => 'JG-001'],
    [
        'Name' => 'Standard Grade',
        'MinSalary' => 30000,
        'MaxSalary' => 80000,
        'Description' => 'Standard Employee Grade',
        'IsActive' => true,
        'CreatedBy' => 1,
        'CreatedOn' => now(),
    ]
);
echo "Job Grade: {$grade->Name}\n";

// 2. Add Overtime Rates
$rates = [
    ['RateMultiplier' => 1.5, 'EffectiveFrom' => now()->subYear(), 'IsActive' => true],
    ['RateMultiplier' => 2.0, 'EffectiveFrom' => now()->subYear(), 'IsActive' => true],
];

foreach ($rates as $r) {
    $exists = OvertimeRate::where('GradeID', $grade->Id)
        ->where('RateMultiplier', $r['RateMultiplier'])
        ->exists();
    
    if (!$exists) {
        OvertimeRate::create(array_merge($r, [
            'GradeID' => $grade->Id,
            'EffectiveTo' => null,
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ]));
        echo "Added Rate: {$r['RateMultiplier']}x\n";
    }
}

// 3. Create Overtime Requests based on Daily Overtime
$overtimeRecords = AttendanceDaily::where('OvertimeHours', '>', 0)
    ->get();

$addedRequests = 0;
foreach ($overtimeRecords as $daily) {
    // Check if request exists manually
    $exists = OvertimeRequest::where('EmployeeID', $daily->EmployeeID)
        ->where('WorkDate', $daily->WorkDate)
        ->exists();

    if (!$exists) {
        $status = ['Pending', 'Approved', 'Rejected'][rand(0, 2)];
        
        OvertimeRequest::create([
            'EmployeeID' => $daily->EmployeeID,
            'WorkDate' => $daily->WorkDate,
            'HoursRequested' => $daily->OvertimeHours,
            'Status' => $status,
            'Reason' => 'Project Deadline / Extra Work',
            'RequestedBy' => $daily->EmployeeID,
            'RequestedOn' => Carbon::parse($daily->WorkDate)->addDay(),
            'ApprovedBy' => ($status != 'Pending') ? 1 : null,
            'ApprovedOn' => ($status != 'Pending') ? Carbon::parse($daily->WorkDate)->addDays(2) : null,
            'ApprovalComment' => ($status == 'Rejected') ? 'Not authorized' : null,
            'CreatedBy' => $daily->EmployeeID,
            'CreatedOn' => Carbon::parse($daily->WorkDate)->addDay(),
        ]);
        $addedRequests++;
    }
}
echo "Added $addedRequests overtime requests.\n";

// 4. Create Attendance Exceptions
// a) Late Arrivals
$lateRecords = AttendanceDaily::where('LateMinutes', '>', 0)->get();
$addedExceptions = 0;

foreach ($lateRecords as $daily) {
    $exists = AttendanceException::where('AttendanceDailyID', $daily->Id)->where('Type', 'Late')->exists();
    if (!$exists) {
        AttendanceException::create([
            'AttendanceDailyID' => $daily->Id,
            'EmployeeID' => $daily->EmployeeID,
            'WorkDate' => $daily->WorkDate,
            'Type' => 'Late',
            'Status' => 'Open',
            'Resolution' => null,
            'ResolvedBy' => null,
            'ResolvedOn' => null,
            'CreatedBy' => 1,
            'CreatedOn' => Carbon::parse($daily->WorkDate)->setTime(10, 0, 0),
        ]);
        $addedExceptions++;
    }
}

// b) Absent
$absentRecords = AttendanceDaily::where('Status', 'Absent')->get();
foreach ($absentRecords as $daily) {
    $exists = AttendanceException::where('AttendanceDailyID', $daily->Id)->where('Type', 'Absent')->exists();
    if (!$exists) {
        AttendanceException::create([
            'AttendanceDailyID' => $daily->Id,
            'EmployeeID' => $daily->EmployeeID,
            'WorkDate' => $daily->WorkDate,
            'Type' => 'Absent',
            'Status' => 'Open', // Usually requires justification
            'Resolution' => null,
            'ResolvedBy' => null,
            'ResolvedOn' => null,
            'CreatedBy' => 1,
            'CreatedOn' => Carbon::parse($daily->WorkDate)->setTime(10, 0, 0),
        ]);
        $addedExceptions++;
    }
}

echo "Added $addedExceptions attendance exceptions.\n";
echo "Done.\n";
