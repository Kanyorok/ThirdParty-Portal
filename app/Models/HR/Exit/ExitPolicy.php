<?php

namespace App\Models\HR\Exit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExitPolicy extends Model
{
    protected $table = 't_HRExitPolicies';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name',
        'EffectiveFrom',
        'EffectiveTo',
        'EmploymentTypes',
        'ContractTypes',
        'ApprovalWorkflow',
        'RedundancyCriteria',
        'TerminalDuesConfig',
        'ChecklistTemplateID',
        'AllowNoticePay',
        'AllowNoticeWaiver',
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
        'AllowNoticePay' => 'boolean',
        'AllowNoticeWaiver' => 'boolean',
        'IsActive' => 'boolean',
    ];

    public function noticePeriods(): HasMany
    {
        return $this->hasMany(ExitPolicyNoticePeriod::class, 'PolicyID');
    }

    public function checklistTemplate(): BelongsTo
    {
        return $this->belongsTo(ExitChecklistTemplate::class, 'ChecklistTemplateID');
    }
}
