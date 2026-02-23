<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class LeaveBalance extends Model
{
    protected $table = 't_HRLeaveBalances';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'LeaveTypeID',
        'Entitlement',
        'Accrued',
        'Taken',
        'Balance',
        'UpdatedBy',
        'UpdatedOn',
    ];

    protected $casts = [
        'Entitlement' => 'decimal:2',
        'Accrued' => 'decimal:2',
        'Taken' => 'decimal:2',
        'Balance' => 'decimal:2',
        'UpdatedOn' => 'datetime',
    ];

    public static function accrueMonthlyForEmployee(Employee $employee, LeaveType $type, string $period, float $days): void
    {
        if ($days <= 0) {
            return;
        }
        $balance = self::firstOrNew([
            'EmployeeID' => $employee->Id,
            'LeaveTypeID' => $type->Id,
        ]);
        if (! $balance->exists) {
            $balance->Entitlement = $type->AnnualEntitlementDays ?? 0;
        }
        $balance->Accrued = ($balance->Accrued ?? 0) + $days;
        $balance->Balance = ($balance->Balance ?? 0) + $days;
        $balance->UpdatedBy = auth()->id();
        $balance->UpdatedOn = now();
        $balance->save();

        LeaveAccrual::create([
            'EmployeeID' => $employee->Id,
            'LeaveTypeID' => $type->Id,
            'AccruedDays' => $days,
            'Period' => $period,
            'CreatedBy' => auth()->id(),
            'CreatedOn' => now(),
        ]);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }

    public function type()
    {
        return $this->belongsTo(LeaveType::class, 'LeaveTypeID');
    }
}
