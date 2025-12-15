<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use App\Models\HR\JobGrade;

class PayrollAllowance extends Model
{
    protected $table = 't_HRPayrollAllowances';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Description',
        'IsTaxable',
        'IsMandatory',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsTaxable' => 'boolean',
        'IsMandatory' => 'boolean',
        'IsActive' => 'boolean',
        'CreatedOn' => 'datetime',
        'ModifiedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function rules()
    {
        return $this->hasMany(PayrollAllowanceRule::class, 'AllowanceID');
    }

    public function grades()
    {
        return $this->belongsToMany(JobGrade::class, 't_HRPayrollAllowanceGrades', 'AllowanceID', 'GradeID');
    }
}
