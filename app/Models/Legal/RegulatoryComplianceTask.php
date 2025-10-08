<?php

namespace App\Models\Legal;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegulatoryComplianceTask extends Model
{
    protected $table = 't_RegulatoryComplianceTasks';
    protected $primaryKey = 'Id';
    public $timestamps = true;

    protected $fillable = [
        'ObligationID',
        'TaskTitle',
        'TaskDescription',
        'TaskDueDate',
        'TaskCompletedDate',
        'TaskStatus',
        'ResponsibleOfficer',
        'EvidenceDocumentPath',
    ];

    public function obligation(): BelongsTo
    {
        return $this->belongsTo(RegulatoryObligation::class, 'ObligationID', 'Id');
    }
}
