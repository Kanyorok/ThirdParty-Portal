<?php

namespace App\Models\Legal;

use Illuminate\Database\Eloquent\Model;

class LoanSecurity extends Model
{
    protected $table = 't_LoanSecurities';
    protected $primaryKey = 'ID';
    public $timestamps = false;

    protected $fillable = [
        'SecurityType',
        'Description',
        'OwnerName',
        'OwnerIDNumber',
        'LoanAccountNumber',
        'Value',
        'Institution',
        'RegistrationDetails',
        'SecurityStatus',
        'Remarks',
        'CreatedBy',
        'CreatedOn',
        'ModifiedBy',
        'ModifiedOn',
        'DeletedBy',
        'DeletedOn',
        'IsActive'
    ];
}
