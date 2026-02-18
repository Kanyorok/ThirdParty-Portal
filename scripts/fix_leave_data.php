<?php

use Illuminate\Support\Facades\DB;
use App\Models\HR\LeaveType;
use App\Models\HR\LeaveRequest;
use App\Models\HR\LeaveBalance;
use App\Models\HR\Employee;
use Carbon\Carbon;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Fixing Leave Data...\n";

DB::beginTransaction();

try {
    // 1. Remove Duplicate Leave Types (IDs 9, 10, 11, 12) created by previous script
    // codes: AL, SL, ML, UL
    $duplicateIds = [9, 10, 11, 12];
    LeaveRequest::whereIn('LeaveTypeID', $duplicateIds)->delete();
    LeaveBalance::whereIn('LeaveTypeID', $duplicateIds)->delete();
    LeaveType::whereIn('Id', $duplicateIds)->delete();
    echo "Removed duplicate Leave Types (IDs 9-12).\n";

    // 2. Remove previously added dummy requests (identified by specific reason)
    LeaveRequest::where('Reason', 'Personal matters / Rest')->delete();
    echo "Removed previous dummy leave requests.\n";

    // 3. Re-seed with proper validation and balance updates
    $employees = Employee::where('IsActive', true)->get();
    
    // Map of valid types
    // 2: Annual (Std), 3: Annual (Mgmt), 4: Sick, 5: Maternity (F), 6: Paternity (M)
    // We'll use a subset for dummy data
    $validTypes = LeaveType::whereIn('Id', [2, 4, 5, 6])->get(); 

    $statuses = ['Pending', 'Approved', 'Rejected'];
    $startYear = Carbon::now()->subMonths(6);

    $count = 0;
    
    foreach ($employees as $emp) {
        // Create 2-3 requests per employee
        for ($i = 0; $i < rand(2, 3); $i++) {
            
            // Gender Filter
            $eligibleTypes = $validTypes->filter(function($type) use ($emp) {
                if ($type->AllowedGender && $emp->Gender) {
                    return strcasecmp($type->AllowedGender, $emp->Gender) === 0;
                }
                return true;
            });

            if ($eligibleTypes->isEmpty()) continue;

            $type = $eligibleTypes->random();
            $status = $statuses[array_rand($statuses)];
            
            $startDate = $startYear->copy()->addDays(rand(1, 150));
            $days = rand(2, 5);
            $endDate = $startDate->copy()->addDays($days - 1);
            
            // Skip weekends logic
            if ($endDate->isWeekend()) $endDate->addDays(2);

            // Check overlap
            $exists = LeaveRequest::where('EmployeeID', $emp->Id)
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->whereBetween('StartDate', [$startDate, $endDate])
                    ->orWhereBetween('EndDate', [$startDate, $endDate]);
                })
                ->exists();

            if (!$exists) {
                // Calculate total days (simple)
                $totalDays = $startDate->diffInDays($endDate) + 1;

                $req = LeaveRequest::create([
                    'EmployeeID' => $emp->Id,
                    'LeaveTypeID' => $type->Id,
                    'StartDate' => $startDate,
                    'EndDate' => $endDate,
                    'TotalDays' => $totalDays,
                    'RelieverID' => $employees->where('Id', '!=', $emp->Id)->random()->Id,
                    'Reason' => 'Personal matters / Rest (Fixed)',
                    'Status' => $status,
                    'RequestedBy' => $emp->Id,
                    'RequestedOn' => $startDate->copy()->subDays(rand(1, 10)),
                    'ApprovedBy' => ($status === 'Approved' || $status === 'Rejected') ? 1 : null,
                    'ApprovedOn' => ($status === 'Approved' || $status === 'Rejected') ? $startDate->copy()->subDays(rand(1, 5)) : null,
                    'CreatedBy' => $emp->Id,
                    'CreatedOn' => $startDate->copy()->subDays(rand(1, 10)),
                ]);

                // *** CRITICAL: Update Balance if Approved ***
                if ($status === 'Approved') {
                    $balance = LeaveBalance::firstOrNew([
                        'EmployeeID' => $emp->Id,
                        'LeaveTypeID' => $type->Id,
                    ]);
                    
                    if (!$balance->exists) {
                        $balance->Entitlement = $type->AnnualEntitlementDays ?? 21; // Default fallback
                        $balance->Accrued = $type->AnnualEntitlementDays ?? 21; // Assume full accrual for simplicity
                        $balance->Taken = 0;
                        $balance->Balance = $balance->Accrued;
                    }

                    $balance->Taken += $totalDays;
                    $balance->Balance -= $totalDays;
                    $balance->save();
                }

                $count++;
            }
        }
    }

    DB::commit();
    echo "Successfully fixed data. Added $count valid leave requests with balance updates.\n";

} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
