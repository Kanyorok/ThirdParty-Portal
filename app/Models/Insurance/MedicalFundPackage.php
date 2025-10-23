<?php

namespace App\Models\Insurance;


use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFundPackage extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_MedicalFundPackages';
    protected $primaryKey = 'Id';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundID','Name','CoverageDescription','Premium','IsCompulsory',
        'CreatedBy','ModifiedBy','DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'MedicalFundPackageId';
    }

    public function fund() { return $this->belongsTo(MedicalFund::class, 'FundId','Id'); }

    public function contributors()
    {
        return $this->belongsToMany(
            MedicalFundContributor::class,
            't_MedicalFundContributorPackages',
            'PackageId',
            'ContributorId'
        )->withPivot(['IsActive','SubscribedOn']);
    }
   
public function coverages() {
    return $this->belongsToMany(Coverage::class, 't_MedicalFundPackageCoverages', 'PackageId', 'CoverageId')
        ->withPivot(['AnnualLimit','PerVisitLimit','WaitingPeriodDays','Scope','IsActive']);
}


}
