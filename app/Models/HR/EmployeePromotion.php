<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;

class EmployeePromotion extends Model
{
    protected $table = 't_HREmployeePromotions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'EmployeeID',
        'FromGradeID',
        'ToGradeID',
        'FromRoleID',
        'ToRoleID',
        'FromSalary',
        'ToSalary',
        'EffectiveDate',
        'Reason',
        'Status',
        'RequestedBy',
        'RequestedOn',
        'ApprovedBy',
        'ApprovedOn',
        'ApprovalComment',
        'CreatedBy',
        'CreatedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'FromSalary' => 'decimal:2',
        'ToSalary' => 'decimal:2',
        'EffectiveDate' => 'date',
        'RequestedOn' => 'datetime',
        'ApprovedOn' => 'datetime',
        'CreatedOn' => 'datetime',
        'DeletedOn' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'EmployeeID');
    }
}
