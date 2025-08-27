<?php

namespace App\Models\Insurance;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFundPackage extends Model
{
    use SoftDeletes;

    protected $table = 't_MedicalFundPackages';
    protected $primaryKey = 'ID';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundID','Name','CoverageDescription','Premium','IsCompulsory',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    protected $casts = [
        'Premium'     => 'decimal:2',
        'IsCompulsory'=> 'boolean',
        'CreatedOn'   => 'datetime',
        'ModifiedOn'  => 'datetime',
        'DeletedOn'   => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($m) { $m->CreatedBy = Auth::id(); $m->ModifiedBy = Auth::id(); });
        static::updating(function ($m) { $m->ModifiedBy = Auth::id(); });
        static::deleting(function ($m) { $m->DeletedBy = Auth::id(); $m->save(); });
    }

    public function fund() { return $this->belongsTo(MedicalFund::class, 'FundID','ID'); }

    public function contributors()
    {
        return $this->belongsToMany(
            MedicalFundContributor::class,
            't_MedicalFundContributorPackages',
            'PackageID',
            'ContributorID'
        )->withPivot(['IsActive','SubscribedOn']);
    }
   
public function coverages() {
    return $this->belongsToMany(Coverage::class, 't_MedicalFundPackageCoverages', 'PackageID', 'CoverageID')
        ->withPivot(['AnnualLimit','PerVisitLimit','WaitingPeriodDays','Scope','IsActive']);
}


}
