<?php

use Illuminate\Support\Facades\DB;
use App\Models\HR\LeaveType;
use App\Models\HR\LeaveRequest;
use App\Models\HR\Employee;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Adding Dummy Leave Requests...\n";

// 1. Ensure Leave Types
$types = [
    ['Code' => 'AL', 'Name' => 'Annual Leave', 'AnnualEntitlementDays' => 21, 'IsPaid' => true],
    ['Code' => 'SL', 'Name' => 'Sick Leave', 'AnnualEntitlementDays' => 14, 'IsPaid' => true],
    ['Code' => 'ML', 'Name' => 'Maternity Leave', 'AnnualEntitlementDays' => 90, 'IsPaid' => true],
    ['Code' => 'UL', 'Name' => 'Unpaid Leave', 'AnnualEntitlementDays' => 0, 'IsPaid' => false],
];

foreach ($types as $t) {
    LeaveType::firstOrCreate(
        ['Code' => $t['Code']],
        array_merge($t, [
            'IsActive' => true,
            'Status' => 'Active',
            'CreatedBy' => 1,
            'CreatedOn' => now(),
        ])
    );
}

$leaveTypes = LeaveType::all();
$employees = Employee::where('IsActive', true)->get();

if ($employees->count() < 2) {
    echo "Need at least 2 employees to assign relievers.\n";
    exit;
}

$statuses = ['Pending', 'Approved', 'Rejected', 'Cancelled'];
$startYear = Carbon::now()->subMonths(6); // Last 6 months

$count = 0;
// Generate 20 requests
for ($i = 0; $i < 20; $i++) {
    $employee = $employees->random();
    $reliever = $employees->where('Id', '!=', $employee->Id)->random();
    $type = $leaveTypes->random();
    $status = $statuses[array_rand($statuses)];

    $startDate = $startYear->copy()->addDays(rand(1, 180));
    $days = rand(1, 5);
    $endDate = $startDate->copy()->addDays($days - 1);

    // Skip weekends for end date roughly (logic simplification)
    if ($endDate->isWeekend()) {
        $endDate->addDays(2);
    }

    // Check overlap
    $exists = LeaveRequest::where('EmployeeID', $employee->Id)
        ->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('StartDate', [$startDate, $endDate])
              ->orWhereBetween('EndDate', [$startDate, $endDate]);
        })
        ->exists();

    if (!$exists) {
        LeaveRequest::create([
            'EmployeeID' => $employee->Id,
            'LeaveTypeID' => $type->Id,
            'StartDate' => $startDate,
            'EndDate' => $endDate,
            'TotalDays' => $startDate->diffInDays($endDate) + 1,
            'RelieverID' => $reliever->Id,
            'Reason' => 'Personal matters / Rest',
            'Status' => $status,
            'RequestedBy' => $employee->Id,
            'RequestedOn' => $startDate->copy()->subDays(rand(1, 10)),
            'ApprovedBy' => ($status === 'Approved' || $status === 'Rejected') ? 1 : null,
            'ApprovedOn' => ($status === 'Approved' || $status === 'Rejected') ? $startDate->copy()->subDays(rand(1, 5)) : null,
            'CreatedBy' => $employee->Id,
            'CreatedOn' => $startDate->copy()->subDays(rand(1, 10)),
        ]);
        $count++;
    }
}

echo "Added $count leave requests.\n";
