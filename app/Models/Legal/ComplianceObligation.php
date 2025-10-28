<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceObligation extends Model
{
    protected $table = 't_ComplianceObligations';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Title', 'Description', 'RegulatorID', 'ComplianceAreaID',
        'EffectiveDate', 'IsActive', 'CreatedBy', 'CreatedOn',
        'ModifiedBy', 'ModifiedOn', 'DeletedBy', 'DeletedOn'
    ];

    public function regulator()
    {
        return $this->belongsTo(RegulatoryBody::class, 'RegulatorID');
    }

    public function area()
    {
        return $this->belongsTo(ComplianceArea::class, 'ComplianceAreaID');
    }

    public function documents()
    {
        return $this->hasMany(ComplianceObligationDocument::class, 'ObligationID');
    }

    public function impacts()
    {
        return $this->hasMany(ComplianceObligationImpact::class, 'ObligationID');
    }
}

