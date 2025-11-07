<?php

namespace App\Models\Legal;

use App\Traits\Model\UserActorTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanSecurity extends Model
{
    use SoftDeletes, UserActorTrait;

    const CREATED_AT = 'CreatedOn';
    const UPDATED_AT = 'ModifiedOn';
    const DELETED_AT = 'DeletedOn';

    protected $table = 't_LegalLoanSecurities';
    protected $primaryKey = 'Id';
    protected $fillable = [
        'SecurityType',
        'OwnerName',
        'OwnerIDNumber',
        'LoanAccountNumber',
        'Value',
        'Institution',
        'RegistrationDetails',
        'Locations',
        'SecurityStatus',
        'Remarks',
        'CreatedBy',
        'ModifiedBy',
    ];

    public static function getPrimaryKey(): string
    {
        return 'LegalLoanSecuritiesId';
    }

}
