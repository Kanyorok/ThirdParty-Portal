<?php

namespace App\Models\HR\Discipline;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DisciplinaryPolicy extends Model
{
    protected $table = 't_HRDisciplinaryPolicies';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'EffectiveFrom',
        'EffectiveTo',
        'EmploymentTypes',
        'ContractTypes',
        'ProgressiveRules',
        'AppealDeadlineDays',
        'RetentionMonths',
        'AllowDirectHearing',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    protected $casts = [
        'EffectiveFrom' => 'date',
        'EffectiveTo' => 'date',
        'AllowDirectHearing' => 'boolean',
        'IsActive' => 'boolean',
    ];

    public function cases(): HasMany
    {
        return $this->hasMany(DisciplinaryCase::class, 'PolicyID');
    }
}
