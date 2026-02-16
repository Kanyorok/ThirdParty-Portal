<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceIncident extends Model
{
    protected $table = 't_ComplianceIncidents';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ObligationID', 'Title', 'Description', 'IncidentDate',
        'SeverityID', 'ResponsibleUserID', 'Status',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn',
    ];

    public function obligation()
    {
        return $this->belongsTo(ComplianceObligation::class, 'ObligationID');
    }

    public function severity()
    {
        return $this->belongsTo(\App\Models\Legal\IncidentSeverityLevel::class, 'SeverityID');
    }

    public function actions()
    {
        return $this->hasMany(ComplianceIncidentAction::class, 'IncidentID');
    }
}
