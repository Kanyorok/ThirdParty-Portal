<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class JobGrade extends Model
{
    protected $table = 't_HRJobGrades';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'MinSalary',
        'MaxSalary',
        'Description',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'MinSalary' => 'decimal:2',
        'MaxSalary' => 'decimal:2',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function roles()
    {
        return $this->hasMany(JobRole::class, 'GradeID');
    }
}
