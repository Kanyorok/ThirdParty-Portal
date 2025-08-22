<?php

namespace App\Models\Insurance;


use Illuminate\Database\Eloquent\Model;
use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsuranceProduct extends Model
{
    use SoftDeletes, UserActorTrait;

    //
    protected $table = 't_InsuranceProducts';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'InsuranceProviderID',
        'Name',
        'Type',
        'Description',
        'IsActive',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];

    public static function getPrimaryKey(): string
    {
        return 'InsuranceProductsId';
    }
     public function provider()
    {
        return $this->belongsTo(InsuranceProvider::class,'InsuranceProviderID','Id');
    }
}
