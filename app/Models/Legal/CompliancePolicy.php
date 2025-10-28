<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class CompliancePolicy extends Model
{
    protected $table = 't_CompliancePolicies';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Title', 'CategoryID', 'ComplianceAreaID', 'EffectiveDate', 'Version',
        'FileName', 'MimeType', 'FilePath', 'IsActive',
        'CreatedBy', 'CreatedOn', 'ModifiedBy', 'ModifiedOn'
    ];

    public function acknowledgments()
    {
        return $this->hasMany(CompliancePolicyAcknowledgment::class, 'PolicyID');
    }
}
