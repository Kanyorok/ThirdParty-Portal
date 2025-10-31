<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceIncidentAction extends Model
{
    protected $table = 't_ComplianceIncidentActions';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'IncidentID', 'RootCause', 'CorrectiveAction',
        'ActionOwnerID', 'DueDate', 'Status', 'CreatedBy', 'CreatedOn'
    ];

    public function incident()
    {
        return $this->belongsTo(ComplianceIncident::class, 'IncidentID');
    }
}
