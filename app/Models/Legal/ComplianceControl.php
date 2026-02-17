<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceControl extends Model
{
    protected $table = 't_ComplianceControls';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Title', 'Description', 'ComplianceAreaID', 'ControlTypeID',
        'OwnerID', 'IsActive', 'CreatedBy', 'CreatedOn',
        'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn',
    ];

    public function area()
    {
        return $this->belongsTo(ComplianceArea::class, 'ComplianceAreaID');
    }

    public function controlType()
    {
        return $this->belongsTo(ControlType::class, 'ControlTypeID');
    }

    public function obligations()
    {
        return $this->belongsToMany(
            ComplianceObligation::class,
            't_ComplianceControlObligations',
            'ControlID',
            'ObligationID'
        );
    }

    public function evidence()
    {
        return $this->hasMany(ComplianceControlEvidence::class, 'ControlID');
    }
}
