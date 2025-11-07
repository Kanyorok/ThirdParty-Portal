<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceObligationImpact extends Model
{
    protected $table = 't_ComplianceObligationImpacts';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'ObligationID', 'ImpactDescription', 'Department',
        'AssessedBy', 'AssessedOn'
    ];
}
