<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobRole extends Model
{
    protected $table = 't_HRJobRoles';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'GradeID',
        'DepartmentID',
        'Description',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public function grade()
    {
        return $this->belongsTo(JobGrade::class, 'GradeID');
    }

    public function department()
    {
        return $this->belongsTo(\App\Models\HRM\Department::class, 'DepartmentID');
    }
}
