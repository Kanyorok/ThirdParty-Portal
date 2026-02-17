<?php

namespace App\Models\HR;

use App\Models\Core\Branch;
use App\Models\HRM\Department;
use Illuminate\Database\Eloquent\Model;

class JobOpening extends Model
{
    protected $table = 't_HRJobOpenings';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'RequisitionID',
        'Code',
        'Title',
        'DepartmentID',
        'BranchID',
        'GradeID',
        'RoleID',
        'EmploymentType',
        'ContractType',
        'Vacancies',
        'Description',
        'Requirements',
        'Status',
        'PublishedOn',
        'CloseDate',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function requisition()
    {
        return $this->belongsTo(JobRequisition::class, 'RequisitionID');
    }

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
        return $this->belongsTo(JobRole::class, 'RoleID', 'id')
            ->withoutGlobalScope('job_roles');
    }

    public function applications()
    {
        return $this->hasMany(JobApplication::class, 'JobOpeningID', 'Id');
    }

    public function interviewSessions()
    {
        return $this->hasMany(InterviewSession::class, 'JobOpeningID', 'Id');
    }

    public function openingQuestions()
    {
        return $this->hasMany(JobOpeningQuestion::class, 'JobOpeningID', 'Id');
    }

    public function questions()
    {
        return $this->belongsToMany(
            JobInterviewQuestion::class,
            't_HRJobOpeningQuestions',
            'JobOpeningID',
            'QuestionID'
        );
    }
}
