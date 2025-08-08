<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LegalCaseOutcome extends Model
{
    protected $table = 't_LegalCaseOutcomes';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'LegalCaseID', 'Outcome', 'JudgmentDate', 'JudgeName',
        'CourtDecision', 'PenaltyAmount', 'Remarks',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn',
        'DeletedBy', 'DeletedOn', 'IsActive',
    ];

    public function case()
    {
        return $this->belongsTo(LegalCase::class, 'LegalCaseID', 'ID');
    }
}
