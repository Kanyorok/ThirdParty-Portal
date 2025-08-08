<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;
use App\Models\Legal\LegalCaseCounsel;
use App\Models\Legal\LegalCaseOutcome;

class LegalCase extends Model
{
    protected $table = 't_LegalCases';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'CaseTitle',
        'CaseNumber',
        'CourtName',
        'FilingDate',
        'OpposingParty',
        'CaseType',
        'Status',
        'Summary',
        'AssignedCounselID',
        'DMSDocID',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    /**
     * One case can have many assigned legal counsels
     */
public function counsels()
{
    return $this->hasMany(LegalCounsel::class, 'LegalCaseID', 'ID');
}
    /**
     * One case has one outcome (judgment or resolution)
     */
    public function outcome()
    {
        return $this->hasOne(LegalCaseOutcome::class, 'LegalCaseID', 'ID');
    }
}
