<?php

namespace App\Models\HR;

use App\Models\Core\Branch;
use App\Models\HRM\Department;
use Illuminate\Database\Eloquent\Model;

class JobRequisition extends Model
{
    protected $table = 't_HRJobRequisitions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Title',
        'DepartmentID',
        'BranchID',
        'GradeID',
        'RoleID',
        'EmploymentType',
        'ContractType',
        'Vacancies',
        'Priority',
        'Justification',
        'Status',
        'RequestedBy',
        'RequestedOn',
        'ApprovedBy',
        'ApprovedOn',
        'RejectedBy',
        'RejectedOn',
        'RejectionReason',
        'ClosedOn',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'DepartmentID');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'BranchID');
    }

    public function grade()
    {
        return $this->belongsTo(JobGrade::class, 'GradeID');
    }

    public function role()
    {
        return $this->belongsTo(JobRole::class, 'RoleID');
    }

    public function openings()
    {
        return $this->hasMany(JobOpening::class, 'RequisitionID', 'Id');
    }
}
