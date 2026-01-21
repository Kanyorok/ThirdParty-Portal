<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use App\Models\HR\EmployeeContact;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\EmployeeSalaryHistory;
use App\Models\HR\EmployeeEducation;
use App\Models\HR\EmployeeWorkingDaySetting;

class Employee extends Model
{
    protected $table = 't_HREmployees';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeNo',
        'FirstName',
        'LastName',
        'OtherNames',
        'Email',
        'Phone',
        'Gender',
        'Religion',
        'DateOfBirth',
        'Address',
        'PhotoPath',
        'BranchID',
        'DepartmentID',
        'GradeID',
        'RoleID',
        'SupervisorID',
        'EmploymentDate',
        'EmploymentType',
        'ContractType',
        'NSSFNo',
        'NHIFNo',
        'KRAPIN',
        'BasicSalary',
        'PaymentMode',
        'BankID',
        'BankBranchID',
        'BankAccount',
        'Status',
        'StatusReason',
        'StatusChangedOn',
        'StatusChangedBy',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'EmploymentDate' => 'date',
        'DateOfBirth'   => 'date',
        'BasicSalary'    => 'decimal:2',
        'StatusChangedOn'=> 'datetime',
        'CreatedOn'      => 'datetime',
        'ModifiedOn'     => 'datetime',
        'DeletedOn'      => 'datetime',
    ];

    // Relationships
    public function branch()
    {
        return $this->belongsTo(\App\Models\Core\Branch::class, 'BranchID');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\HRM\Department::class, 'DepartmentID');
    }

    public function grade()
    {
        return $this->belongsTo(JobGrade::class, 'GradeID');
    }

    public function role()
    {
        return $this->belongsTo(JobRole::class, 'RoleID');
    }

    public function supervisor()
    {
        return $this->belongsTo(self::class, 'SupervisorID');
    }

    public function bank()
    {
        return $this->belongsTo(\App\Models\Finance\Bank::class, 'BankID', 'BankID');
    }

    public function bankBranch()
    {
        return $this->belongsTo(\App\Models\Finance\BankBranch::class, 'BankBranchID', 'BranchID');
    }

    public function attendanceDaily()
    {
        return $this->hasMany(AttendanceDaily::class, 'EmployeeID');
    }

    public function attendanceLogs()
    {
        return $this->hasMany(AttendanceLog::class, 'EmployeeID');
    }

    public function contacts()
    {
        return $this->hasMany(EmployeeContact::class, 'EmployeeID')->whereNull('DeletedOn');
    }

    public function documents()
    {
        return $this->hasMany(EmployeeDocument::class, 'EmployeeID')->whereNull('DeletedOn');
    }

    public function salaryHistory()
    {
        return $this->hasMany(EmployeeSalaryHistory::class, 'EmployeeID')->orderByDesc('EffectiveFrom');
    }

    public function education()
    {
        return $this->hasMany(EmployeeEducation::class, 'EmployeeID')->whereNull('DeletedOn');
    }

    public function workingDayOverrides()
    {
        return $this->hasMany(EmployeeWorkingDaySetting::class, 'EmployeeID');
    }
}
