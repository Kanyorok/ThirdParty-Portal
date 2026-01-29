<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;

class Coverage extends Model
{
    protected $table = 't_Coverages';
    protected $primaryKey = 'Id';
    protected $fillable = ['Code','Name','Description','IsActive','CreatedBy','ModifiedBy'];
    public $timestamps = false; // using explicit columns above

    public function packages()
    {
        return $this->belongsToMany(MedicalFundPackage::class, 't_MedicalFundPackageCoverages', 'CoverageId', 'PackageId')
            ->withPivot(['AnnualLimit','PerVisitLimit','WaitingPeriodDays','Scope','IsActive']);
    }
}
