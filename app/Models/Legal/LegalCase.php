<?php

namespace App\Models\Legal;

use App\Models\Core\Approval\CodeDetail;
use Illuminate\Database\Eloquent\Model;
use App\Models\Legal\LegalCaseCounsel;
use App\Models\Legal\LegalCaseOutcome;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class LegalCase extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalCases';
    protected $primaryKey = 'Id';
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
        'CaseDMSDocID',
        'IsActive',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalCasesId';
    }

    /**
     * One case can have many assigned legal counsels
     */
    public function counsels()
    {
        return $this->hasMany(LegalCaseCounsel::class, 'LegalCaseID', 'Id');
    }

    /**
     * One case has one outcome (judgment or resolution)
     */
    public function outcome()
    {
        return $this->hasOne(LegalCaseOutcome::class, 'LegalCaseID', 'Id');
    }

    public function cases()
    {
        return $this->belongsTo(CodeDetail::class, 'CaseType', 'Value');
    }

}
