<?php

namespace App\Models\Insurance;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class MedicalFundBeneficiary extends Model
{
    use SoftDeletes, UserActorTrait;

    protected $table = 't_MedicalFundBeneficiaries';
    protected $primaryKey = 'Id';

    public $timestamps = true;
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $fillable = [
        'FundId',
        'ContributorId',   
        'FullName',
        'Relationship',
        'DateOfBirth',
        'NationalID',
        'Contact',
        'IsActive',
        'CreatedBy','CreatedOn','ModifiedBy','ModifiedOn','DeletedBy','DeletedOn'
    ];

    public static function getPrimaryKey(): string
    {
        return 'MedicalFundBeneficiaryId';
    }

    public function fund()
    {
        return $this->belongsTo(MedicalFund::class, 'FundId', 'Id');
    }

    public function contributor()
    { 
        return $this->belongsTo(MedicalFundContributor::class, 'ContributorId','Id'); 
    }
}
