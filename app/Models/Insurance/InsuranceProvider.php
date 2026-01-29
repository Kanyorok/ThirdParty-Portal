<?php

namespace App\Models\Insurance;

use App\Models\Core\Country;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceProvider extends Model
{
    use SoftDeletes;
    use UserActorTrait;


    protected $table = 't_InsuranceProviders';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    public const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InsuranceProviderNO',
        'Name',
        'Country',
        'ContactPerson',
        'Email',
        'Phone',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'InsuranceProvidersId';
    }

    public function getProductByProvider()
    {
        return $this->hasMany(InsuranceProduct::class, 'InsuranceproviderID', 'Id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'Country', 'Id');
    }
}
