<?php

namespace App\Models\Insurance;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Model\UserActorTrait;

class PropertyBlock extends Model
{
    use SoftDeletes, UserActorTrait;
    //
    protected $table = 't_BancassuranceCustomers';
    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'ReferralID',
        'FullName',
        'NationalID',
        'KRAPIN',
        'DateOfBirth',
        'Gender',
        'MaritalStatus',
        'PhoneNumber',
        'Email',
        'Address',
        'Occupation',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
    
    public static function getPrimaryKey(): string
    {
        return 'BancassuranceCustomersId';
    }

}