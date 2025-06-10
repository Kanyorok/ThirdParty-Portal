<?php

namespace App\Models\PropertyManagement;

use Illuminate\Database\Eloquent\Model;

class PropertyTenantClearance extends Model
{
    //
    protected $table = 't_TenantClearance';
    public const CREATED_AT = 'CreatedOn';
    public const UPDATED_AT = 'ModifiedOn';
    const string DELETED_AT = 'DeletedOn';
    protected $primaryKey = 'Id';

    protected $fillable = [
        'Tenant',
        'ExitDate',
        'FinalInspection',
        'AllDuesPaid',
        'KeysReturned',
        'DepositRefunded',
        'AdditionalNotes',
        'CreatedBy',
        'ModifiedBy',
        'DeletedBy'
    ];
}
