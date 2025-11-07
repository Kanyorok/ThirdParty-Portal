<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceControlObligation extends Model
{
    protected $table = 't_ComplianceControlObligations';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['ControlID', 'ObligationID'];
}
