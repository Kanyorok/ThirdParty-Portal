<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class CompliancePolicyAcknowledgment extends Model
{
    protected $table = 't_CompliancePolicyAcknowledgments';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = ['PolicyID','UserID','AcknowledgedOn'];

    public function policy()
    {
        return $this->belongsTo(CompliancePolicy::class,'PolicyID');
    }
}
