<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;

class DisciplinarySanction extends Model
{
    protected $table = 't_HRDisciplinarySanctions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Code',
        'Name',
        'Description',
        'IsSuspension',
        'SuspensionWithoutPay',
        'AffectsPayroll',
        'BlocksLeave',
        'UpdatesEmploymentStatus',
        'EmploymentStatus',
        'DefaultDurationDays',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'IsSuspension' => 'boolean',
        'SuspensionWithoutPay' => 'boolean',
        'AffectsPayroll' => 'boolean',
        'BlocksLeave' => 'boolean',
        'UpdatesEmploymentStatus' => 'boolean',
        'IsActive' => 'boolean',
    ];
}
