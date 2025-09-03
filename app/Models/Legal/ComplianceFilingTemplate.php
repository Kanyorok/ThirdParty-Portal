<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class ComplianceFilingTemplate extends Model
{
    protected $table = 't_ComplianceFilingTemplates';
    protected $primaryKey = 'Id';
    public $timestamps = false;

    protected $fillable = [
        'Name','FilingTypeID','RegulatorID','FormatID',
        'Frequency','DueDay','PortalURL','Description','IsActive',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn'
    ];

    public function regulator()
    {
        return $this->belongsTo(RegulatoryBody::class,'RegulatorID');
    }

    public function type()
    {
        return $this->belongsTo(FilingType::class,'FilingTypeID');
    }

    public function format()
    {
        return $this->belongsTo(FileFormat::class,'FormatID');
    }

    public function filings()
    {
        return $this->hasMany(ComplianceFiling::class,'TemplateID');
    }
}
