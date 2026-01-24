<?php

namespace App\Models\HR;

use App\Models\Auth\User;
use App\Models\Core\Branch;
use App\Models\DMS\Image;
use App\Models\HR\EmployeeContact;
use App\Models\HR\EmployeeDocument;
use App\Models\HR\EmployeeSalaryHistory;
use App\Models\HR\EmployeeEducation;
use App\Models\HR\EmployeeWorkingDaySetting;
use App\Models\HR\JobGrade;
use App\Models\HR\JobRole;
use App\Models\HRM\Committee;
use App\Models\HRM\Department;
use App\Traits\Model\DocumentsTrait;
use App\Traits\Model\ImageTrait;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use UserActorTrait, SoftDeletes, ImageTrait, DocumentsTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_HREmployees';
    protected $primaryKey = 'Id';

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

    /**
     * Get the primary key name for workflow mapping
     */
    public static function getPrimaryKey(): string
    {
        return (new self())->getRouteKeyName();
    }

    /**
     * Get the route key name (for URL routing)
     */
    public function getRouteKeyName(): string
    {
        return 'EmployeeNo';
    }

    /**
     * Get the full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->FirstName . ' ' . ($this->OtherNames ? $this->OtherNames . ' ' : '') . $this->LastName);
    }

    /**
     * Get the name/identifier for images (required by ImageTrait)
     */
    protected function getImageName(): string
    {
        return $this->full_name;
    }

    // Relationships
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'BranchID', 'Id')->withTrashed();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'DepartmentID', 'Id')->withTrashed();
    }

    public function user(): HasOne
    {
        // t_Users.EmployeeId -> t_HREmployees.Id
        return $this->hasOne(User::class, 'EmployeeId', 'Id')->withTrashed();
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Image::class, 'ImageId', 'ImageID');
    }

    public function committees(): BelongsToMany
    {
        return $this->belongsToMany(Committee::class, 't_Committee_Employee', 'EmployeeId', 'CommitteeId')
            ->withPivot(['CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn']);
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
