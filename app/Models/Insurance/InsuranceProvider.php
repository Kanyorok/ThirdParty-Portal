<?php

namespace App\Models\Insurance;

use App\Models\Core\Country;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceProvider extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_InsuranceProviders';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
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
        'DeletedBy'
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
