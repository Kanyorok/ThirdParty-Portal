<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RegulatoryObligation extends Model
{
    protected $table = 't_RegulatoryObligations';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'ObligationTitle',
        'ObligationDescription',
        'RegulatoryBody',
        'ObligationType',
        'EffectiveDate',
        'DueDate',
        'IsRecurring',
        'RecurrenceType',
        'Status',
        'ComplianceArea',
        'AttachmentPath',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(RegulatoryComplianceTask::class, 'ObligationID', 'Id');
    }
}
